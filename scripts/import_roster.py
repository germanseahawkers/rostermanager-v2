#!/usr/bin/env python3
from __future__ import annotations

import argparse
import csv
import json
import ssl
import sys
from dataclasses import dataclass
from dataclasses import replace
from pathlib import Path
from typing import Iterable
from urllib.parse import urlsplit
from urllib.request import Request, urlopen
from zipfile import ZIP_DEFLATED
from zipfile import ZipFile


DEFAULT_TEAM_ID = "26"
DEFAULT_OUTPUT = "database/imports/seahawks_active_roster.csv"
DEFAULT_ENDPOINT = "https://site.api.espn.com/apis/site/v2/sports/football/nfl/teams/{team_id}/roster"
USER_AGENT = "rostermanager-v2-importer/1.0"
ACTIVE_SECTION_KEYS = {"offense", "defense", "specialTeam"}
TEAM_SLUG_TO_ID = {
    "arizona-cardinals": "22",
    "atlanta-falcons": "1",
    "baltimore-ravens": "33",
    "buffalo-bills": "2",
    "carolina-panthers": "29",
    "chicago-bears": "3",
    "cincinnati-bengals": "4",
    "cleveland-browns": "5",
    "dallas-cowboys": "6",
    "denver-broncos": "7",
    "detroit-lions": "8",
    "green-bay-packers": "9",
    "houston-texans": "34",
    "indianapolis-colts": "11",
    "jacksonville-jaguars": "30",
    "kansas-city-chiefs": "12",
    "las-vegas-raiders": "13",
    "los-angeles-chargers": "24",
    "los-angeles-rams": "14",
    "miami-dolphins": "15",
    "minnesota-vikings": "16",
    "new-england-patriots": "17",
    "new-orleans-saints": "18",
    "new-york-giants": "19",
    "new-york-jets": "20",
    "philadelphia-eagles": "21",
    "pittsburgh-steelers": "23",
    "san-francisco-49ers": "25",
    "seattle-seahawks": "26",
    "tampa-bay-buccaneers": "27",
    "tennessee-titans": "10",
    "washington-commanders": "28",
}


@dataclass
class PlayerRow:
    name: str
    player_id: str
    position: str
    abbr: str
    experience: str
    weight_kg: int | None
    height_cm: int | None
    image: str
    ordering: int


def build_ssl_context(insecure: bool) -> ssl.SSLContext:
    if insecure:
        return ssl._create_unverified_context()

    try:
        import certifi  # type: ignore

        return ssl.create_default_context(cafile=certifi.where())
    except Exception:
        return ssl.create_default_context()


def fetch_json(url: str, insecure: bool = False) -> dict:
    request = Request(url, headers={"User-Agent": USER_AGENT, "Accept": "application/json"})
    with urlopen(request, context=build_ssl_context(insecure)) as response:
        return json.load(response)


def fetch_binary(url: str, insecure: bool = False) -> tuple[bytes, str]:
    request = Request(url, headers={"User-Agent": USER_AGENT})
    with urlopen(request, context=build_ssl_context(insecure)) as response:
        content_type = response.headers.get_content_type()
        return response.read(), content_type


def parse_weight_to_kg(weight_lbs: object) -> int | None:
    if weight_lbs in (None, ""):
        return None

    try:
        return round(float(weight_lbs) * 0.45359237)
    except (TypeError, ValueError):
        return None


def parse_height_to_cm(height_inches: object) -> int | None:
    if height_inches in (None, ""):
        return None

    try:
        return round(float(height_inches) * 2.54)
    except (TypeError, ValueError):
        return None


def build_endpoint(team_id: str, endpoint_template: str) -> str:
    return endpoint_template.format(team_id=team_id)


def normalize_team_slug(value: str) -> str:
    return value.strip().lower().replace("_", "-").replace(" ", "-")


def team_slug_lookup() -> dict[str, str]:
    lookup: dict[str, str] = {}

    for slug, team_id in TEAM_SLUG_TO_ID.items():
        variants = {slug}
        if "-" in slug:
            variants.add(slug.split("-")[-1])

        for variant in variants:
            lookup[normalize_team_slug(variant)] = team_id

    return lookup


def resolve_team_id(team_id: str, team_slug: str) -> str:
    if team_id.strip():
        return team_id.strip()

    if not team_slug.strip():
        return DEFAULT_TEAM_ID

    lookup = team_slug_lookup()
    normalized_slug = normalize_team_slug(team_slug)

    if normalized_slug in lookup:
        return lookup[normalized_slug]

    available = ", ".join(sorted(TEAM_SLUG_TO_ID.keys()))
    raise RuntimeError(f"Unknown team slug '{team_slug}'. Try one of: {available}")


def build_rows(payload: dict, ordering_step: int = 10) -> list[PlayerRow]:
    athletes = payload.get("athletes")
    if not isinstance(athletes, list):
        raise RuntimeError("Unexpected ESPN response: missing athletes list.")

    rows: list[PlayerRow] = []

    for section in athletes:
        if not isinstance(section, dict):
            continue

        section_key = str(section.get("position") or "")
        if section_key not in ACTIVE_SECTION_KEYS:
            continue

        items = section.get("items")
        if not isinstance(items, list):
            continue

        for item in items:
            if not isinstance(item, dict):
                continue

            status = item.get("status") or {}
            status_type = ""
            if isinstance(status, dict):
                status_type = str(status.get("type") or "").lower()
            if status_type and status_type != "active":
                continue

            full_name = str(item.get("fullName") or item.get("displayName") or "").strip()
            if not full_name:
                continue

            position_info = item.get("position") or {}
            position = ""
            if isinstance(position_info, dict):
                position = str(position_info.get("abbreviation") or "").strip().upper()
            if not position:
                continue

            experience = item.get("experience") or {}
            experience_years = ""
            if isinstance(experience, dict):
                years = experience.get("years")
                if years is not None:
                    experience_years = str(years)

            headshot = item.get("headshot") or {}
            image_url = ""
            if isinstance(headshot, dict):
                image_url = str(headshot.get("href") or "").strip()

            rows.append(
                PlayerRow(
                    name=full_name,
                    player_id=str(item.get("id") or ""),
                    position=position,
                    abbr=position,
                    experience=experience_years,
                    weight_kg=parse_weight_to_kg(item.get("weight")),
                    height_cm=parse_height_to_cm(item.get("height")),
                    image=image_url,
                    ordering=len(rows) * ordering_step + ordering_step,
                )
            )

    if not rows:
        raise RuntimeError("No active roster rows were found in the ESPN response.")

    return rows


def slugify_filename(value: str) -> str:
    characters = []

    for char in value.lower():
        if char.isalnum():
            characters.append(char)
        elif characters and characters[-1] != "-":
            characters.append("-")

    return "".join(characters).strip("-") or "player"


def image_extension(image_url: str, content_type: str) -> str:
    mime_map = {
        "image/jpeg": "jpg",
        "image/png": "png",
        "image/webp": "webp",
    }

    if content_type in mime_map:
        return mime_map[content_type]

    suffix = Path(urlsplit(image_url).path).suffix.lower()
    if suffix in {".jpg", ".jpeg", ".png", ".webp"}:
        return "jpg" if suffix == ".jpeg" else suffix.lstrip(".")

    return "png"


def default_zip_output_path(csv_output: Path) -> Path:
    return csv_output.with_name(csv_output.stem + "_images.zip")


def build_local_image_filename(row: PlayerRow, content_type: str) -> str:
    extension = image_extension(row.image, content_type)
    base_name = slugify_filename(row.name)
    player_id = row.player_id or base_name
    return f"{base_name}-{player_id}.{extension}"


def localize_images(rows: list[PlayerRow], zip_output: Path, insecure: bool = False) -> tuple[list[PlayerRow], int]:
    localized_rows: list[PlayerRow] = []
    images_to_zip: list[tuple[str, bytes]] = []

    for row in rows:
        if not row.image:
            localized_rows.append(row)
            continue

        image_bytes, content_type = fetch_binary(row.image, insecure=insecure)
        filename = build_local_image_filename(row, content_type)
        images_to_zip.append((filename, image_bytes))
        localized_rows.append(replace(row, image=filename))

    if images_to_zip:
        zip_output.parent.mkdir(parents=True, exist_ok=True)
        with ZipFile(zip_output, "w", compression=ZIP_DEFLATED) as archive:
            for filename, image_bytes in images_to_zip:
                archive.writestr(filename, image_bytes)

    return localized_rows, len(images_to_zip)


def write_csv(rows: Iterable[PlayerRow], output_path: Path) -> None:
    output_path.parent.mkdir(parents=True, exist_ok=True)

    with output_path.open("w", newline="", encoding="utf-8") as handle:
        writer = csv.writer(handle)
        writer.writerow(["id", "name", "position", "abbr", "experience", "weight_kg", "height_cm", "image", "ordering"])

        for row in rows:
            writer.writerow([
                row.player_id,
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
    parser = argparse.ArgumentParser(description="Import an ESPN NFL roster into a CSV for rostermanager-v2.")
    parser.add_argument("--team-id", default="", help="ESPN NFL team ID")
    parser.add_argument("--team-slug", default="", help="Team slug like seattle-seahawks or seahawks")
    parser.add_argument("--endpoint", default=DEFAULT_ENDPOINT, help="Endpoint template with {team_id} placeholder")
    parser.add_argument("--output", default=DEFAULT_OUTPUT, help="CSV output path")
    parser.add_argument("--local-images", action="store_true", help="Download player headshots, write local filenames into the CSV and create a ZIP archive")
    parser.add_argument("--zip-output", default="", help="ZIP output path for --local-images (defaults next to the CSV)")
    parser.add_argument("--insecure", action="store_true", help="Disable SSL certificate verification for local troubleshooting")
    args = parser.parse_args()

    try:
        resolved_team_id = resolve_team_id(args.team_id, args.team_slug)
        endpoint = build_endpoint(resolved_team_id, args.endpoint)
        payload = fetch_json(endpoint, insecure=args.insecure)
        rows = build_rows(payload)
        output_path = Path(args.output)
        image_count = 0

        if args.local_images:
            zip_output = Path(args.zip_output) if args.zip_output else default_zip_output_path(output_path)
            rows, image_count = localize_images(rows, zip_output, insecure=args.insecure)

        write_csv(rows, output_path)
    except Exception as exc:
        print(f"Error: {exc}", file=sys.stderr)
        return 1

    print(f"Wrote {len(rows)} player rows to {args.output}")
    if args.local_images and image_count > 0:
        zip_output = args.zip_output or str(default_zip_output_path(Path(args.output)))
        print(f"Wrote {image_count} player image(s) to {zip_output}")
    elif args.local_images:
        print("No player images were available to archive.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
