import argparse
import json
import os
import subprocess
import sys
import time
import unicodedata


VALID_MODES = {"fetch", "scrape-all", "scrape-one", "scrape-missing"}


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(
        description="Run OLSX brand file jobs with sequential retry and delay handling."
    )
    parser.add_argument("--type", default="car", help="Vehicle type, e.g. car, truck, moto")
    parser.add_argument(
        "--mode",
        default="scrape-all",
        choices=sorted(VALID_MODES),
        help="fetch, scrape-all, scrape-one or scrape-missing",
    )
    parser.add_argument("--brand", help="Single brand name for scrape-one")
    parser.add_argument("--delay", type=float, default=1.0, help="Delay in seconds between brands")
    parser.add_argument(
        "--retries",
        type=int,
        default=2,
        help="Additional retries per brand after the first failed attempt",
    )
    parser.add_argument("--brands-path", help="Override brand list path")
    parser.add_argument("--output-dir", help="Override output directory")
    parser.add_argument("--status-path", help="Override status file path")
    parser.add_argument("--overwrite", choices=["overwrite", "skip", "update"], help="Overwrite strategy")
    parser.add_argument("--resume", choices=["true", "false"], help="Override resume behavior")
    parser.add_argument("--limit-brands", type=int, help="Optional limit for fetch mode")
    parser.add_argument(
        "--fetch-first",
        action="store_true",
        help="Refresh brand list before scraping brands",
    )
    return parser.parse_args()


def slugify_brand_name(value: str | None) -> str:
    if not value:
        return ""

    replacements = {
        "ä": "ae",
        "ö": "oe",
        "ü": "ue",
        "ß": "ss",
        "Ä": "ae",
        "Ö": "oe",
        "Ü": "ue",
    }
    normalized = "".join(replacements.get(char, char) for char in value)
    normalized = unicodedata.normalize("NFKD", normalized)
    normalized = normalized.encode("ascii", "ignore").decode("ascii")
    normalized = normalized.lower().strip()
    normalized = normalized.replace("&", " and ")

    output = []
    last_was_dash = False
    for char in normalized:
        if char.isalnum():
            output.append(char)
            last_was_dash = False
            continue

        if not last_was_dash:
            output.append("-")
            last_was_dash = True

    return "".join(output).strip("-")


def default_paths(scraper_type: str) -> tuple[str, str, str]:
    base_dir = os.path.join(os.getcwd(), "data")
    return (
        os.path.join(base_dir, "brands", f"{scraper_type}_brands.json"),
        os.path.join(base_dir, "output", scraper_type),
        os.path.join(base_dir, "tmp", f"scrape-status-{scraper_type}.json"),
    )


def load_brand_list(brands_path: str) -> list[str]:
    if not os.path.exists(brands_path):
        raise FileNotFoundError(f"Brand list not found: {brands_path}")

    with open(brands_path, "r", encoding="utf-8") as handle:
        payload = json.load(handle)

    if not isinstance(payload, list):
        raise ValueError(f"Brand list must be a JSON array: {brands_path}")

    brands = []
    seen = set()
    for entry in payload:
        if isinstance(entry, str):
            brand_name = " ".join(entry.split()).strip()
        elif isinstance(entry, dict):
            brand_name = " ".join(str(entry.get("name", "")).split()).strip()
        else:
            brand_name = ""

        brand_slug = slugify_brand_name(brand_name)
        if not brand_name or not brand_slug or brand_slug in seen:
            continue

        seen.add(brand_slug)
        brands.append(brand_name)

    return sorted(brands, key=slugify_brand_name)


def load_status_map(status_path: str) -> dict:
    if not os.path.exists(status_path):
        return {}

    with open(status_path, "r", encoding="utf-8") as handle:
        payload = json.load(handle)

    if not isinstance(payload, dict):
        return {}

    return {str(key): str(value) for key, value in payload.items()}


def is_resume_enabled(args: argparse.Namespace) -> bool:
    if args.resume is not None:
        return args.resume == "true"
    return args.mode in {"scrape-all", "scrape-missing"}


def output_is_valid(output_path: str) -> bool:
    if not os.path.exists(output_path):
        return False

    try:
        with open(output_path, "r", encoding="utf-8") as handle:
            payload = json.load(handle)
        return (
            isinstance(payload, dict)
            and bool(payload.get("brand_name"))
            and bool(payload.get("file_slug"))
            and isinstance(payload.get("models"), list)
        )
    except (OSError, json.JSONDecodeError, TypeError, ValueError):
        return False


def run_spider(scraper_type: str, spider_mode: str, extra_args: list[str]) -> int:
    command = [
        "scrapy",
        "crawl",
        "olsx_brand_files_spider",
        "-a",
        f"type={scraper_type}",
        "-a",
        f"mode={spider_mode}",
    ]
    command.extend(extra_args)
    print(f"$ {' '.join(command)}", flush=True)
    completed = subprocess.run(command, check=False)
    return completed.returncode


def run_fetch(args: argparse.Namespace, brands_path: str) -> int:
    extra_args = []
    if args.brands_path:
        extra_args.extend(["-a", f"brands_path={brands_path}"])
    if args.limit_brands:
        extra_args.extend(["-a", f"limit_brands={args.limit_brands}"])

    return run_spider(args.type, "brands:fetch", extra_args)


def build_spider_args(args: argparse.Namespace, brands_path: str, output_dir: str, status_path: str, brand_name: str) -> list[str]:
    extra_args = [
        "-a",
        f"brand={brand_name}",
        "-a",
        f"brands_path={brands_path}",
        "-a",
        f"output_dir={output_dir}",
        "-a",
        f"status_path={status_path}",
    ]

    if args.overwrite:
        extra_args.extend(["-a", f"overwrite={args.overwrite}"])
    if args.resume:
        extra_args.extend(["-a", f"resume={args.resume}"])

    return extra_args


def should_skip_brand(mode: str, brand_slug: str, output_dir: str, status_path: str) -> bool:
    output_path = os.path.join(output_dir, f"{brand_slug}.json")
    if mode == "scrape-missing":
        return output_is_valid(output_path)

    status_map = load_status_map(status_path)
    return status_map.get(brand_slug) == "done" and output_is_valid(output_path)


def should_skip_in_runner(args: argparse.Namespace, brand_slug: str, output_dir: str, status_path: str) -> bool:
    if args.mode == "scrape-missing":
        return should_skip_brand(args.mode, brand_slug, output_dir, status_path)

    if args.mode == "scrape-all" and is_resume_enabled(args):
        return should_skip_brand(args.mode, brand_slug, output_dir, status_path)

    return False


def run_brand_attempts(args: argparse.Namespace, brands_path: str, output_dir: str, status_path: str, brand_name: str) -> bool:
    brand_slug = slugify_brand_name(brand_name)

    if should_skip_in_runner(args, brand_slug, output_dir, status_path):
        print(f"skip {brand_name} ({brand_slug})", flush=True)
        return True

    max_attempts = max(args.retries, 0) + 1
    for attempt in range(1, max_attempts + 1):
        print(f"brand {brand_name} attempt {attempt}/{max_attempts}", flush=True)
        code = run_spider(
            args.type,
            "brands:scrape-one",
            build_spider_args(args, brands_path, output_dir, status_path, brand_name),
        )

        output_path = os.path.join(output_dir, f"{brand_slug}.json")
        status_map = load_status_map(status_path)
        if code == 0 and status_map.get(brand_slug) == "done" and output_is_valid(output_path):
            print(f"done {brand_name} -> {output_path}", flush=True)
            return True

        print(f"failed {brand_name} attempt {attempt}/{max_attempts}", flush=True)
        if attempt < max_attempts:
            time.sleep(max(args.delay, 0))

    return False


def main() -> int:
    args = parse_args()
    brands_path, output_dir, status_path = default_paths(args.type)

    if args.brands_path:
        brands_path = args.brands_path
    if args.output_dir:
        output_dir = args.output_dir
    if args.status_path:
        status_path = args.status_path

    os.makedirs(os.path.dirname(brands_path), exist_ok=True)
    os.makedirs(output_dir, exist_ok=True)
    os.makedirs(os.path.dirname(status_path), exist_ok=True)

    if args.mode == "fetch":
        return run_fetch(args, brands_path)

    if args.fetch_first:
        fetch_code = run_fetch(args, brands_path)
        if fetch_code != 0:
            return fetch_code

    if args.mode == "scrape-one":
        if not args.brand:
            raise SystemExit("--brand is required for --mode scrape-one")
        return 0 if run_brand_attempts(args, brands_path, output_dir, status_path, args.brand) else 1

    brands = load_brand_list(brands_path)

    total = len(brands)
    failures = []

    for index, brand_name in enumerate(brands, start=1):
        print(f"[{index}/{total}] {brand_name}", flush=True)
        success = run_brand_attempts(args, brands_path, output_dir, status_path, brand_name)
        if not success:
            failures.append(brand_name)

        if index < total and args.delay > 0:
            time.sleep(args.delay)

    if failures:
        print("failed brands:", ", ".join(failures), flush=True)
        return 1

    print(f"completed {total} brands", flush=True)
    return 0


if __name__ == "__main__":
    sys.exit(main())
