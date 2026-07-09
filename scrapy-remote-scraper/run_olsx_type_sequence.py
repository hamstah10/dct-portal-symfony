import argparse
import json
import os
import subprocess
import sys
from datetime import datetime


DEFAULT_TYPES = ["truck", "moto", "atv", "agricultural", "boat"]
VALID_STATUSES = ("done", "failed", "skipped", "pending")


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(
        description="Run multiple OLSX scraper types sequentially and write summary logs."
    )
    parser.add_argument(
        "--types",
        nargs="+",
        default=DEFAULT_TYPES,
        help="Ordered scraper types to process sequentially.",
    )
    parser.add_argument("--delay", type=float, default=0.5, help="Delay in seconds between brands.")
    parser.add_argument(
        "--retries",
        type=int,
        default=1,
        help="Additional retries per brand after the first failed attempt.",
    )
    return parser.parse_args()


def run_command(command: list[str]) -> int:
    print(f"$ {' '.join(command)}", flush=True)
    completed = subprocess.run(command, check=False)
    return completed.returncode


def base_data_dir() -> str:
    return os.path.join(os.getcwd(), "data")


def type_paths(scraper_type: str) -> tuple[str, str, str, str]:
    data_dir = base_data_dir()
    return (
        os.path.join(data_dir, "brands", f"{scraper_type}_brands.json"),
        os.path.join(data_dir, "output", scraper_type),
        os.path.join(data_dir, "tmp", f"scrape-status-{scraper_type}.json"),
        os.path.join(data_dir, "logs", f"{scraper_type}_run_summary.log"),
    )


def load_json(path: str, fallback):
    if not os.path.exists(path):
        return fallback

    with open(path, "r", encoding="utf-8") as handle:
        return json.load(handle)


def write_summary_log(scraper_type: str, brands_path: str, output_dir: str, status_path: str, summary_path: str) -> None:
    status_data = load_json(status_path, {})
    brand_list = load_json(brands_path, [])
    output_files = [
        entry
        for entry in os.listdir(output_dir)
        if entry.endswith(".json") and os.path.isfile(os.path.join(output_dir, entry))
    ] if os.path.isdir(output_dir) else []

    counts = {status: 0 for status in VALID_STATUSES}
    if isinstance(status_data, dict):
        for value in status_data.values():
            if value in counts:
                counts[value] += 1

    total = len(status_data) if isinstance(status_data, dict) and status_data else len(brand_list)
    timestamp = datetime.now().astimezone().isoformat(timespec="seconds")

    os.makedirs(os.path.dirname(summary_path), exist_ok=True)
    with open(summary_path, "w", encoding="utf-8") as handle:
        handle.write(
            f"{timestamp} type={scraper_type} mode=scrape-all "
            f"result=completed done={counts['done']} failed={counts['failed']} "
            f"skipped={counts['skipped']} pending={counts['pending']} total={total} "
            f"outputs={len(output_files)}\n"
        )
        handle.write(f"status_file=data/tmp/scrape-status-{scraper_type}.json\n")
        handle.write(f"brands_file=data/brands/{scraper_type}_brands.json\n")
        handle.write(f"output_dir=data/output/{scraper_type}\n")
        handle.write(f"spider_log=data/logs/olsx_brand_files_{scraper_type}.log\n")


def run_type(scraper_type: str, delay: float, retries: int) -> int:
    brands_path, output_dir, status_path, summary_path = type_paths(scraper_type)

    fetch_code = run_command(
        [
            sys.executable,
            "run_olsx_brand_jobs.py",
            "--mode",
            "fetch",
            "--type",
            scraper_type,
        ]
    )
    if fetch_code != 0:
        return fetch_code

    scrape_code = run_command(
        [
            sys.executable,
            "run_olsx_brand_jobs.py",
            "--mode",
            "scrape-all",
            "--type",
            scraper_type,
            "--delay",
            str(delay),
            "--retries",
            str(retries),
        ]
    )
    write_summary_log(scraper_type, brands_path, output_dir, status_path, summary_path)
    return scrape_code


def main() -> int:
    args = parse_args()

    for scraper_type in args.types:
        print(f"== Processing {scraper_type} ==", flush=True)
        exit_code = run_type(scraper_type, args.delay, args.retries)
        if exit_code != 0:
            print(f"Stopped after {scraper_type} with exit code {exit_code}", flush=True)
            return exit_code

    print("All requested types completed.", flush=True)
    return 0


if __name__ == "__main__":
    sys.exit(main())
