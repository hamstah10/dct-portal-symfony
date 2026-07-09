#!/usr/bin/env python3
import argparse
import json
import mimetypes
import os
import sys
import urllib.parse
import urllib.request
from pathlib import Path


BASE_DIR = Path(__file__).resolve().parents[1]
SUPPORTED_EXTENSIONS = {".svg", ".png", ".jpg", ".jpeg", ".webp", ".gif", ".avif"}


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(
        description="Download brand logos from existing OLSX brand JSON files."
    )
    parser.add_argument("--type", required=True, help="Vehicle type, e.g. atv, car, truck")
    parser.add_argument("--input-dir", help="Override JSON input directory")
    parser.add_argument("--logo-dir", help="Override logo output directory")
    parser.add_argument("--overwrite", action="store_true", help="Download logo again if file already exists")
    return parser.parse_args()


def extension_from_url(url: str) -> str:
    parsed = urllib.parse.urlparse(url)
    extension = Path(parsed.path).suffix.lower()
    return extension if extension in SUPPORTED_EXTENSIONS else ""


def extension_from_content_type(content_type: str | None) -> str:
    if not content_type:
        return ""

    extension = mimetypes.guess_extension(content_type.split(";", 1)[0].strip())
    if extension == ".jpe":
        return ".jpg"
    return extension or ""


def relative_to_base(path: Path) -> str:
    try:
        return str(path.resolve().relative_to(BASE_DIR))
    except ValueError:
        return str(path)


def load_payload(path: Path) -> dict | None:
    try:
        with path.open("r", encoding="utf-8") as handle:
            payload = json.load(handle)
    except (OSError, json.JSONDecodeError) as exc:
        print(f"skip unreadable {path}: {exc}", file=sys.stderr)
        return None

    if not isinstance(payload, dict):
        print(f"skip non-object {path}", file=sys.stderr)
        return None

    return payload


def write_payload(path: Path, payload: dict) -> None:
    tmp_path = path.with_suffix(f"{path.suffix}.tmp")
    with tmp_path.open("w", encoding="utf-8") as handle:
        json.dump(payload, handle, ensure_ascii=False, indent=2)
        handle.write("\n")
    os.replace(tmp_path, path)


def download_logo(url: str, file_slug: str, logo_dir: Path, overwrite: bool) -> Path:
    extension = extension_from_url(url) or ".png"
    logo_path = logo_dir / f"{file_slug}{extension}"

    if logo_path.exists() and logo_path.stat().st_size > 0 and not overwrite:
        return logo_path

    request = urllib.request.Request(
        url,
        headers={
            "User-Agent": "Mozilla/5.0 (compatible; TFD OLSX logo downloader; +https://tuningfux.de)",
        },
    )

    with urllib.request.urlopen(request, timeout=20) as response:
        content = response.read()
        if not content:
            raise RuntimeError("empty response body")

        content_type = response.headers.get("Content-Type")
        detected_extension = extension_from_url(url) or extension_from_content_type(content_type) or ".png"
        logo_path = logo_dir / f"{file_slug}{detected_extension}"
        tmp_path = logo_path.with_suffix(f"{logo_path.suffix}.tmp")

        with tmp_path.open("wb") as handle:
            handle.write(content)
        os.replace(tmp_path, logo_path)

    return logo_path


def process_file(path: Path, logo_dir: Path, overwrite: bool) -> tuple[str, bool]:
    payload = load_payload(path)
    if payload is None:
        return path.name, False

    logo_url = payload.get("brand_logo")
    file_slug = payload.get("file_slug") or path.stem
    if not logo_url or not file_slug:
        print(f"skip missing brand_logo {path}", file=sys.stderr)
        return path.name, False

    logo_path = download_logo(str(logo_url), str(file_slug), logo_dir, overwrite)
    payload["brand_logo_file"] = relative_to_base(logo_path)
    write_payload(path, payload)
    return path.name, True


def main() -> int:
    args = parse_args()
    input_dir = Path(args.input_dir) if args.input_dir else BASE_DIR / "data" / "output" / args.type
    logo_dir = Path(args.logo_dir) if args.logo_dir else BASE_DIR / "data" / "logos" / args.type

    if not input_dir.exists():
        print(f"input directory not found: {input_dir}", file=sys.stderr)
        return 1

    logo_dir.mkdir(parents=True, exist_ok=True)
    json_files = sorted(input_dir.glob("*.json"))
    if not json_files:
        print(f"no JSON files found in {input_dir}", file=sys.stderr)
        return 1

    ok = 0
    failed = 0
    for path in json_files:
        try:
            name, success = process_file(path, logo_dir, args.overwrite)
        except Exception as exc:
            failed += 1
            print(f"failed {path.name}: {exc}", file=sys.stderr)
            continue

        if success:
            ok += 1
            print(f"ok {name}")
        else:
            failed += 1

    print(f"done type={args.type} ok={ok} failed={failed} logos={logo_dir}")
    return 0 if failed == 0 else 1


if __name__ == "__main__":
    sys.exit(main())
