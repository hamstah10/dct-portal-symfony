#!/usr/bin/env python3
"""Teilt eine Scraper-Ausgabe (große JSON-Datei) in eine Übersicht und eine Datei pro Marke.

Aufruf: split_scraper_output.py <quelle.json> <zielordner>

Ergebnis:
  <zielordner>/index.json            Übersicht: Marken mit Zählungen + Quelle
  <zielordner>/brands/<slug>.json    komplette Marke (Modelle, Versionen, Motoren, ...)

Die Übersicht wird zuletzt geschrieben. Fehlt sie, ist die Aufteilung unvollständig.
"""
import json
import os
import re
import shutil
import sys
from datetime import datetime, timezone


def count_brand(brand):
    counts = {"model_count": 0, "version_count": 0, "engine_count": 0,
              "controller_count": 0, "option_count": 0, "stage_count": 0}
    for model in brand.get("models") or []:
        counts["model_count"] += 1
        for version in model.get("versions") or []:
            counts["version_count"] += 1
            for engine in version.get("engines") or []:
                counts["engine_count"] += 1
                counts["option_count"] += len(engine.get("options") or [])
                counts["stage_count"] += len(engine.get("stages") or [])
                for group in engine.get("controllers") or []:
                    counts["controller_count"] += len((group or {}).get("items") or [])
    return counts


SCHEMA = 2
FILE_RE = re.compile(r"^olsx_brands_([a-z]+)_(\d{8})_(\d{6})(?:_([a-z]+))?\.json$")


class StatsAcc:
    """Sammelt die Leistungswerte Marke für Marke. So muss nie die ganze Ausgabe im Speicher liegen."""

    def __init__(self):
        self.total = 0
        self.orig_ps, self.orig_nm, self.gains_ps, self.gains_nm = [], [], [], []
        self.with_stages = self.with_options = self.with_controllers = self.with_tools = self.checked = 0
        self.dyno = {"stage-1": 0, "stage-2": 0}

    def add(self, brand):
        for m in (brand.get("models") or []):
            for v in (m.get("versions") or []):
                for e in (v.get("engines") or []):
                    self._add_engine(e)

    def _add_engine(self, e):
        self.total += 1
        original = e.get("original") or {}
        if original.get("power_ps") is not None:
            self.orig_ps.append(original["power_ps"])
        if original.get("torque_nm") is not None:
            self.orig_nm.append(original["torque_nm"])

        stages = [st for st in (e.get("stages") or []) if st.get("power_ps") is not None]
        if stages:
            self.with_stages += 1
            best = max(stages, key=lambda st: st["power_ps"])
            if original.get("power_ps") is not None:
                self.gains_ps.append(best["power_ps"] - original["power_ps"])
            best_nm = [st["torque_nm"] for st in stages if st.get("torque_nm") is not None]
            if best_nm and original.get("torque_nm") is not None:
                self.gains_nm.append(max(best_nm) - original["torque_nm"])

        if e.get("options"):
            self.with_options += 1
        groups = e.get("controllers") or []
        if groups:
            self.with_controllers += 1
        if any(item.get("tools") for g in groups for item in (g or {}).get("items") or []):
            self.with_tools += 1
        if e.get("stages_checked"):
            self.checked += 1
        # Dyno-Charts je Stage (nur Motoren mit Messpunkten zählen)
        charts = e.get("dyno_charts") or {}
        for stage in self.dyno:
            if any((charts.get(stage) or {}).get(curve) for curve in ("original", "optimized")):
                self.dyno[stage] += 1

    def finish(self):
        total = self.total

        def summary(values):
            if not values:
                return None
            return {"count": len(values), "min": min(values), "max": max(values),
                    "avg": round(sum(values) / len(values), 1)}

        def pct(n):
            return round(100 * n / total, 1) if total else 0.0

        def missing(count):
            return {"count": count, "missing": total - count, "pct": pct(count)}

        return {
            "dyno_charts": {"engines": total, "stage_1": missing(self.dyno["stage-1"]), "stage_2": missing(self.dyno["stage-2"])},
            "power_ps": summary(self.orig_ps),
            "torque_nm": summary(self.orig_nm),
            "stage_gain_ps": summary(self.gains_ps),
            "stage_gain_nm": summary(self.gains_nm),
            "coverage": {
                "engines": total,
                "with_stages_pct": pct(self.with_stages),
                "with_options_pct": pct(self.with_options),
                "with_controllers_pct": pct(self.with_controllers),
                "with_tools_pct": pct(self.with_tools),
                "checked_pct": pct(self.checked),
            },
        }


def stats_for(brands):
    """Leistungswerte, Stage-Gewinn und Abdeckung über alle Motoren (Einzeldatei-Modus)."""
    acc = StatsAcc()
    for b in brands:
        acc.add(b)
    return acc.finish()


def runs_for(series_dir, type_name):
    """Liste der Scraper-Läufe aus den Dateinamen (ohne die Dateien zu öffnen)."""
    runs = []
    if not series_dir or not os.path.isdir(series_dir):
        return runs
    for name in os.listdir(series_dir):
        match = FILE_RE.match(name)
        if not match or match.group(1) != type_name:
            continue
        started = datetime.strptime(match.group(2) + match.group(3), "%Y%m%d%H%M%S")
        suffix = match.group(4) or ""
        kind = {"filled": "Nachfüllen", "partial": "abgebrochen"}.get(suffix, "Crawl")
        path = os.path.join(series_dir, name)
        runs.append({
            "file": name,
            "started_at": started.isoformat(),
            "kind": kind,
            "size_mb": round(os.path.getsize(path) / 1048576, 1),
        })
    return sorted(runs, key=lambda r: r["started_at"])


def safe_slug(value):
    slug = str(value or "").strip().lower()
    if not re.fullmatch(r"[a-z0-9][a-z0-9_-]*", slug):
        return None
    return slug


def main():
    if len(sys.argv) not in (3, 4):
        print(__doc__)
        return 2
    source, target = sys.argv[1], sys.argv[2]
    series_dir = sys.argv[3] if len(sys.argv) == 4 else os.path.dirname(os.path.normpath(source))

    acc = StatsAcc()
    summaries = []
    if os.path.isdir(source):
        # Ordner mit einer JSON-Datei je Marke (z. B. Dyno, Tuning-Datenbank): eine Datei nach der anderen
        files = sorted(n for n in os.listdir(source) if n.endswith(".json"))
        source_mtime = int(max((os.path.getmtime(os.path.join(source, n)) for n in files), default=os.path.getmtime(source)))
        source_name = os.path.basename(os.path.normpath(source))
        type_name = "td" if "tuning_datenbank" in os.path.normpath(source) else "dyno"
    else:
        files = None
        with open(source, encoding="utf-8") as f:
            brands = json.load(f)
        source_mtime = int(os.path.getmtime(source))
        source_name = os.path.basename(source)
        type_name = re.match(r"olsx_brands_([a-z]+)_", source_name).group(1)

    brands_dir = os.path.join(target, "brands")
    if os.path.isdir(brands_dir):
        shutil.rmtree(brands_dir)
    os.makedirs(brands_dir, exist_ok=True)

    def handle(brand):
        slug = safe_slug(brand.get("brand_slug"))
        if slug is None:
            return
        with open(os.path.join(brands_dir, f"{slug}.json"), "w", encoding="utf-8") as f:
            json.dump(brand, f, ensure_ascii=False)
        acc.add(brand)
        summaries.append({
            "slug": slug,
            "name": brand.get("brand_name"),
            "logo": brand.get("brand_logo"),
            **count_brand(brand),
        })

    if files is not None:
        for name in files:
            with open(os.path.join(source, name), encoding="utf-8") as f:
                handle(json.load(f))
    else:
        for brand in brands:
            handle(brand)

    totals = {key: sum(item[key] for item in summaries) for key in
              ("model_count", "version_count", "engine_count", "controller_count", "option_count", "stage_count")}

    runs = runs_for(series_dir, type_name) if type_name not in ("dyno", "td") else []
    if type_name == "dyno":
        # Lauf-Informationen für Dyno stehen in crawl_info.json neben dem Ordner
        info_path = os.path.join(os.path.dirname(os.path.normpath(source)), "crawl_info.json")
        if os.path.isfile(info_path):
            with open(info_path, encoding="utf-8") as f:
                runs = json.load(f).get("runs", [])
            runs = [{"file": "crawl_info.json", "started_at": r["started_at"], "kind": r.get("kind", "Crawl"),
                     "size_mb": 0, "ended_at": r.get("ended_at"), "duration": r.get("duration")} for r in runs]
    index = {
        "schema": SCHEMA,
        "source": source_name,
        "source_mtime": source_mtime,
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "brand_count": len(summaries),
        "totals": totals,
        "stats": acc.finish(),
        "runs": runs,
        "date_from": runs[0]["started_at"] if runs else None,
        "date_to": runs[-1].get("ended_at") or datetime.fromtimestamp(source_mtime).isoformat() if runs else datetime.fromtimestamp(source_mtime).isoformat(),
        "brands": sorted(summaries, key=lambda b: (b["name"] or b["slug"]).lower()),
    }
    tmp = os.path.join(target, "index.json.tmp")
    with open(tmp, "w", encoding="utf-8") as f:
        json.dump(index, f, ensure_ascii=False)
    os.replace(tmp, os.path.join(target, "index.json"))
    print(f"ok brands={len(summaries)} totals={totals}")
    return 0


if __name__ == "__main__":
    sys.exit(main())
