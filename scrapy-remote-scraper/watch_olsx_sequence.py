import argparse
import json
import os
import subprocess
import sys
import time
from datetime import datetime


DEFAULT_TYPES = ["truck", "moto", "atv", "agricultural", "boat"]


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(
        description="Watch and repair OLSX type sequence runs."
    )
    parser.add_argument("--types", nargs="+", default=DEFAULT_TYPES, help="Ordered scraper types to monitor.")
    parser.add_argument("--interval", type=int, default=180, help="Check interval in seconds.")
    parser.add_argument("--delay", type=float, default=0.5, help="Delay passed to the sequence runner.")
    parser.add_argument("--retries", type=int, default=1, help="Retries passed to the sequence runner.")
    return parser.parse_args()


def base_dir() -> str:
    return os.getcwd()


def data_dir() -> str:
    return os.path.join(base_dir(), "data")


def log_path() -> str:
    return os.path.join(data_dir(), "logs", "olsx_sequence_watchdog.log")


def pid_path() -> str:
    return os.path.join(data_dir(), "tmp", "olsx_sequence_watchdog.pid")


def ensure_dirs() -> None:
    os.makedirs(os.path.join(data_dir(), "logs"), exist_ok=True)
    os.makedirs(os.path.join(data_dir(), "tmp"), exist_ok=True)


def log(message: str) -> None:
    timestamp = datetime.now().astimezone().isoformat(timespec="seconds")
    line = f"{timestamp} {message}"
    print(line, flush=True)
    with open(log_path(), "a", encoding="utf-8") as handle:
        handle.write(line + "\n")


def load_json(path: str, fallback):
    if not os.path.exists(path):
        return fallback
    try:
        with open(path, "r", encoding="utf-8") as handle:
            return json.load(handle)
    except (OSError, json.JSONDecodeError):
        return fallback


def save_json(path: str, payload) -> None:
    with open(path, "w", encoding="utf-8") as handle:
        json.dump(payload, handle, ensure_ascii=False, indent=2)


def type_paths(scraper_type: str) -> tuple[str, str, str]:
    return (
        os.path.join(data_dir(), "brands", f"{scraper_type}_brands.json"),
        os.path.join(data_dir(), "output", scraper_type),
        os.path.join(data_dir(), "tmp", f"scrape-status-{scraper_type}.json"),
    )


def output_is_valid(path: str) -> bool:
    payload = load_json(path, None)
    return (
        isinstance(payload, dict)
        and bool(payload.get("brand_name"))
        and bool(payload.get("file_slug"))
        and isinstance(payload.get("models"), list)
    )


def sequence_pids() -> list[int]:
    command = [
        "pgrep",
        "-f",
        r"python3 run_olsx_type_sequence.py|/usr/bin/python3 run_olsx_type_sequence.py",
    ]
    completed = subprocess.run(command, check=False, capture_output=True, text=True)
    pids = []
    if completed.returncode not in {0, 1}:
        return pids
    for line in completed.stdout.splitlines():
        line = line.strip()
        if not line:
            continue
        try:
            pids.append(int(line.split()[0]))
        except ValueError:
            continue
    return pids


def count_outputs(output_dir: str) -> int:
    if not os.path.isdir(output_dir):
        return 0
    return sum(
        1
        for entry in os.listdir(output_dir)
        if entry.endswith(".json") and os.path.isfile(os.path.join(output_dir, entry))
    )


def repair_type(scraper_type: str) -> dict:
    brands_path, output_dir, status_path = type_paths(scraper_type)
    brands = load_json(brands_path, [])
    status_data = load_json(status_path, {})

    if not isinstance(status_data, dict):
        status_data = {}

    repairs = 0
    for entry in brands if isinstance(brands, list) else []:
        if not isinstance(entry, str):
            continue
        brand_name = " ".join(entry.split()).strip()
        if not brand_name:
            continue
        slug = slugify_brand_name(brand_name)
        output_path = os.path.join(output_dir, f"{slug}.json")
        if output_is_valid(output_path) and status_data.get(slug) != "done":
            status_data[slug] = "done"
            repairs += 1

    if repairs:
        save_json(status_path, status_data)
        log(f"repaired type={scraper_type} marked_done={repairs}")

    counts = {key: 0 for key in ("done", "failed", "skipped", "pending")}
    for value in status_data.values():
        if value in counts:
            counts[value] += 1

    total = len(status_data) if status_data else (len(brands) if isinstance(brands, list) else 0)
    counts["total"] = total
    counts["outputs"] = count_outputs(output_dir)
    counts["repairs"] = repairs
    return counts


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
    normalized = normalized.encode("ascii", "ignore").decode("ascii").lower().strip()
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


def all_types_complete(types: list[str]) -> bool:
    for scraper_type in types:
        brands_path, output_dir, status_path = type_paths(scraper_type)
        brands = load_json(brands_path, [])
        status_data = load_json(status_path, {})
        if not isinstance(brands, list) or not brands:
            return False
        if not isinstance(status_data, dict):
            return False
        target_total = len([entry for entry in brands if isinstance(entry, str) and entry.strip()])
        done_total = sum(value == "done" for value in status_data.values())
        if done_total < target_total:
            return False
        if count_outputs(output_dir) < target_total:
            return False
    return True


def start_sequence(types: list[str], delay: float, retries: int) -> int:
    command = [
        sys.executable,
        "run_olsx_type_sequence.py",
        "--types",
        *types,
        "--delay",
        str(delay),
        "--retries",
        str(retries),
    ]
    process = subprocess.Popen(command, cwd=base_dir())
    log(f"started sequence pid={process.pid}")
    return process.pid


def main() -> int:
    args = parse_args()
    ensure_dirs()

    with open(pid_path(), "w", encoding="utf-8") as handle:
        handle.write(str(os.getpid()))

    log(f"watchdog started interval={args.interval} types={','.join(args.types)}")

    while True:
        try:
            summaries = []
            for scraper_type in args.types:
                counts = repair_type(scraper_type)
                summaries.append(
                    f"{scraper_type}:done={counts['done']},failed={counts['failed']},"
                    f"pending={counts['pending']},outputs={counts['outputs']}"
                )

            pids = sequence_pids()
            if pids:
                log(f"sequence alive pids={','.join(str(pid) for pid in pids)} {' | '.join(summaries)}")
            elif all_types_complete(args.types):
                log(f"sequence complete {' | '.join(summaries)}")
            else:
                new_pid = start_sequence(args.types, args.delay, args.retries)
                log(f"sequence restarted pid={new_pid} {' | '.join(summaries)}")
        except Exception as exc:
            log(f"watchdog error={exc}")

        time.sleep(max(args.interval, 30))


if __name__ == "__main__":
    sys.exit(main())
