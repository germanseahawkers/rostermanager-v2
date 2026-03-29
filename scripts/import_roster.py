#!/usr/bin/env python3
from __future__ import annotations

import argparse
import csv
import json
import ssl
import sys
from dataclasses import dataclass
from pathlib import Path
from typing import Iterable
from urllib.request import Request, urlopen


DEFAULT_TEAM_ID = "26"
DEFAULT_OUTPUT = "database/imports/seahawks_active_roster.csv"
DEFAULT_ENDPOINT = "https://site.api.espn.com/apis/site/v2/sports/football/nfl/teams/{team_id}/roster"
USER_AGENT = "rostermanager-v2-importer/1.0"
ACTIVE_SECTION_KEYS = {"offense", "defense", "specialTeam"}


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
    parser = argparse.ArgumentParser(description="Import an ESPN NFL roster into a CSV for rostermanager-v2.")
    parser.add_argument("--team-id", default=DEFAULT_TEAM_ID, help="ESPN NFL team ID")
    parser.add_argument("--endpoint", default=DEFAULT_ENDPOINT, help="Endpoint template with {team_id} placeholder")
    parser.add_argument("--output", default=DEFAULT_OUTPUT, help="CSV output path")
    parser.add_argument("--insecure", action="store_true", help="Disable SSL certificate verification for local troubleshooting")
    args = parser.parse_args()

    try:
        endpoint = build_endpoint(args.team_id, args.endpoint)
        payload = fetch_json(endpoint, insecure=args.insecure)
        rows = build_rows(payload)
        write_csv(rows, Path(args.output))
    except Exception as exc:
        print(f"Error: {exc}", file=sys.stderr)
        return 1

    print(f"Wrote {len(rows)} player rows to {args.output}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
