#!/usr/bin/env python3
from __future__ import annotations

import argparse
import csv
import html
import re
import ssl
import sys
from dataclasses import dataclass
from html.parser import HTMLParser
from pathlib import Path
from typing import Iterable
from urllib.parse import urljoin
from urllib.request import Request, urlopen


DEFAULT_URL = "https://www.seahawks.com/team/players-roster/"
DEFAULT_OUTPUT = "database/imports/seahawks_active_roster.csv"
USER_AGENT = "rostermanager-v2-importer/1.0"


@dataclass
class PlayerRow:
    name: str
    position: str
    abbr: str
    experience: str
    weight_kg: int | None
    height_cm: int | None
    image: str
    ordering: int


class TextExtractor(HTMLParser):
    def __init__(self) -> None:
        super().__init__()
        self.parts: list[str] = []

    def handle_data(self, data: str) -> None:
        value = data.strip()
        if value:
            self.parts.append(value)

    def text_lines(self) -> list[str]:
        return self.parts


def build_ssl_context(insecure: bool) -> ssl.SSLContext:
    if insecure:
        return ssl._create_unverified_context()

    try:
        import certifi  # type: ignore

        return ssl.create_default_context(cafile=certifi.where())
    except Exception:
        return ssl.create_default_context()


def fetch(url: str, insecure: bool = False) -> str:
    request = Request(url, headers={"User-Agent": USER_AGENT})
    with urlopen(request, context=build_ssl_context(insecure)) as response:
        return response.read().decode("utf-8", errors="replace")


def extract_text_lines(html_source: str) -> list[str]:
    parser = TextExtractor()
    parser.feed(html_source)
    return parser.text_lines()


def extract_active_names(html_source: str) -> list[str]:
    names: list[str] = []
    seen: set[str] = set()

    for match in re.finditer(r'href="(/team/players-roster/[^"/]+/)"[^>]*>([^<]+)</a>', html_source):
        name = html.unescape(match.group(2)).strip()
        if not name or name in seen or "/" in name:
            continue
        seen.add(name)
        names.append(name)

    return names


def extract_active_block_lines(html_source: str) -> list[str]:
    lines = extract_text_lines(html_source)
    start_index: int | None = None

    for index in range(len(lines) - 8):
        window = [line.strip().lower() for line in lines[index:index + 9]]
        if window[:8] == ["active", "player", "#", "pos", "ht", "wt", "age", "exp"] or window[:7] == ["player", "#", "pos", "ht", "wt", "age", "exp"]:
            start_index = index + 9
            break

    if start_index is None:
        for index in range(len(lines) - 3):
            window = " ".join(" ".join(lines[index:index + 6]).split()).lower()
            if "active" in window and "player" in window and "pos" in window and "ht" in window and "wt" in window:
                start_index = index + 6
                break

    if start_index is None:
        raise RuntimeError("Could not find active roster section in source page.")

    active_lines: list[str] = []

    for line in lines[start_index:]:
        if line in {"Advertising", "### CLUB PARTNERS", "Club Links", "NFL Clubs"}:
            break
        active_lines.append(line)

    return active_lines


def parse_height_to_cm(height: str) -> int | None:
    height = height.strip()
    match = re.match(r"^(\d+)-(\d+)$", height)
    if not match:
        return None
    feet = int(match.group(1))
    inches = int(match.group(2))
    return round((feet * 30.48) + (inches * 2.54))


def parse_weight_to_kg(weight: str) -> int | None:
    weight = weight.strip()
    if not weight.isdigit():
        return None
    return round(int(weight) * 0.45359237)


def extract_player_image(profile_html: str, base_url: str) -> str:
    patterns = [
        r'<meta\s+property="og:image"\s+content="([^"]+)"',
        r'<meta\s+name="twitter:image"\s+content="([^"]+)"',
        r'"image":"([^"]+)"',
    ]

    for pattern in patterns:
        match = re.search(pattern, profile_html, flags=re.IGNORECASE)
        if match:
            return html.unescape(urljoin(base_url, match.group(1)))

    return ""


def extract_profile_urls(html_source: str, base_url: str) -> dict[str, str]:
    profiles: dict[str, str] = {}

    for match in re.finditer(r'href="(/team/players-roster/[^"/]+/)"[^>]*>([^<]+)</a>', html_source):
        name = html.unescape(match.group(2)).strip()
        href = urljoin(base_url, match.group(1))
        if name and name not in profiles:
            profiles[name] = href

    return profiles


def build_rows(roster_html: str, base_url: str, ordering_step: int = 10, insecure: bool = False) -> list[PlayerRow]:
    lines = extract_active_block_lines(roster_html)
    profiles = extract_profile_urls(roster_html, base_url)
    rows: list[PlayerRow] = []

    index = 0
    while index < len(lines):
        name = lines[index].strip()

        if not name or name in {"Advertising", "Club Links", "NFL Clubs"}:
            break

        if index + 7 >= len(lines):
            break

        token_slice = [item.strip() for item in lines[index + 1:index + 8]]
        first_token = token_slice[0]

        if first_token.isdigit():
            position = token_slice[1]
            height_raw = token_slice[2]
            weight_raw = token_slice[3]
            experience = token_slice[5]
            step = 8
        else:
            position = token_slice[0]
            height_raw = token_slice[1]
            weight_raw = token_slice[2]
            experience = token_slice[4]
            step = 7

        if not re.match(r"^[A-Z/]+$", position) or not re.match(r"^\d+-\d+$", height_raw) or not weight_raw.isdigit():
            index += 1
            continue

        profile_url = profiles.get(name, "")
        image_url = ""

        if profile_url:
            try:
                image_url = extract_player_image(fetch(profile_url, insecure=insecure), profile_url)
            except Exception:
                image_url = ""

        rows.append(
            PlayerRow(
                name=name,
                position=position,
                abbr=position,
                experience=experience,
                weight_kg=parse_weight_to_kg(weight_raw),
                height_cm=parse_height_to_cm(height_raw),
                image=image_url,
                ordering=len(rows) * ordering_step + ordering_step,
            )
        )

        index += step

    return rows


def write_csv(rows: Iterable[PlayerRow], output_path: Path) -> None:
    output_path.parent.mkdir(parents=True, exist_ok=True)

    with output_path.open("w", newline="", encoding="utf-8") as handle:
        writer = csv.writer(handle)
        writer.writerow(["name", "position", "abbr", "experience", "weight_kg", "height_cm", "image", "ordering"])

        for row in rows:
            writer.writerow([
                row.name,
                row.position,
                row.abbr,
                row.experience,
                row.weight_kg or "",
                row.height_cm or "",
                row.image,
                row.ordering,
            ])


def main() -> int:
    parser = argparse.ArgumentParser(description="Import an NFL roster page into a CSV for rostermanager-v2.")
    parser.add_argument("--url", default=DEFAULT_URL, help="Roster page URL")
    parser.add_argument("--output", default=DEFAULT_OUTPUT, help="CSV output path")
    parser.add_argument("--insecure", action="store_true", help="Disable SSL certificate verification for local troubleshooting")
    args = parser.parse_args()

    try:
        roster_html = fetch(args.url, insecure=args.insecure)
        rows = build_rows(roster_html, args.url, insecure=args.insecure)
        if not rows:
            raise RuntimeError("No roster rows could be extracted.")
        write_csv(rows, Path(args.output))
    except Exception as exc:
        print(f"Error: {exc}", file=sys.stderr)
        return 1

    print(f"Wrote {len(rows)} player rows to {args.output}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
