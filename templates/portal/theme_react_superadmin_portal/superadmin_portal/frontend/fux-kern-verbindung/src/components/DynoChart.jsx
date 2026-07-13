
import React, { useEffect, useMemo, useState } from "react";
import {
  Chart as ChartJS,
  LinearScale,
  PointElement,
  LineElement,
  Tooltip,
  Legend,
} from "chart.js";
import { Line } from "react-chartjs-2";
import "./DynoChart.css";

ChartJS.register(LinearScale, PointElement, LineElement, Tooltip, Legend);

const FUEL_PRESETS = {
  diesel: { label: "Diesel", defaultMaxRpm: 5000, rpmStep: 500, powerPad: 50, torquePad: 100 },
  petrol: { label: "Benzin", defaultMaxRpm: 7000, rpmStep: 1000, powerPad: 50, torquePad: 80 },
  unknown: { label: "Motor", defaultMaxRpm: 6500, rpmStep: 1000, powerPad: 50, torquePad: 100 },
};

function normalizeFuel(input) {
  const value = String(input || "").toLowerCase();
  if (value.includes("diesel") || value.includes("30d") || value.includes("40d") || value.includes("tdi") || value.includes("cdi") || value.includes("dci") || value.includes("hdi")) return "diesel";
  if (value.includes("benzin") || value.includes("petrol") || value.includes("gasoline") || value.includes("tsi") || value.includes("tfsi") || value.includes("turbo") || value.includes("m2") || value.includes("m3") || value.includes("m4")) return "petrol";
  return "unknown";
}

function cleanPoints(points = [], addZeroPoint = true) {
  if (!Array.isArray(points)) return [];
  const cleaned = points
    .filter((p) => Array.isArray(p) && p.length >= 2)
    .map(([rpm, value]) => ({ x: Number(rpm), y: Number(value) }))
    .filter((p) => Number.isFinite(p.x) && Number.isFinite(p.y))
    .sort((a, b) => a.x - b.x);
  if (addZeroPoint && cleaned.length && cleaned[0].x > 0) return [{ x: 0, y: 0 }, ...cleaned];
  return cleaned;
}

function maxY(points) { return points.length ? Math.max(...points.map((p) => p.y)) : 0; }
function maxX(points) { return points.length ? Math.max(...points.map((p) => p.x)) : 0; }
function roundUp(value, step) { return Math.ceil(Math.max(value || 0, step) / step) * step; }
function displayNumber(value) { return Number.isFinite(Number(value)) ? Math.round(Number(value)) : "-"; }

function getStages(apiData) {
  return Array.isArray(apiData?.stages) ? apiData.stages.filter((s) => s?.dynoChart) : [];
}

function findStage(stages, selectedStageId, selectedStageName) {
  if (!stages.length) return null;
  if (selectedStageId !== undefined && selectedStageId !== null) {
    const byId = stages.find((s) => String(s.id) === String(selectedStageId));
    if (byId) return byId;
  }
  if (selectedStageName) {
    const byName = stages.find((s) => String(s.name).toLowerCase() === String(selectedStageName).toLowerCase());
    if (byName) return byName;
  }
  return stages[0];
}

function buildTitle(apiData) {
  return [apiData?.brand?.name, apiData?.series?.name, apiData?.model?.name, apiData?.engine?.name]
    .filter(Boolean)
    .join(" · ");
}

export default function DynoChart({
  apiData,
  selectedStageId,
  selectedStageName,
  title,
  subtitle,
  height = 460,
  showTabs = true,
  addZeroPoint = true,
  className = "",
}) {
  const stages = getStages(apiData);
  const initialStage = findStage(stages, selectedStageId, selectedStageName);
  const [activeStageId, setActiveStageId] = useState(initialStage?.id ?? null);

  useEffect(() => {
    if (!stages.length) return;
    const preferredStage = findStage(stages, selectedStageId, selectedStageName);

    setActiveStageId(currentId => {
      const currentStage = stages.find((stage) => String(stage.id) === String(currentId));
      if (currentStage) {
        return currentId;
      }

      return preferredStage?.id ?? stages[0]?.id ?? null;
    });
  }, [stages, selectedStageId, selectedStageName]);

  const activeStage = useMemo(() => {
    return stages.find((s) => String(s.id) === String(activeStageId)) || findStage(stages, selectedStageId, selectedStageName);
  }, [stages, activeStageId, selectedStageId, selectedStageName]);

  const fuelKey = normalizeFuel(`${apiData?.engine?.fuel || ""} ${apiData?.engine?.name || ""}`);
  const fuel = FUEL_PRESETS[fuelKey] || FUEL_PRESETS.unknown;
  const dyno = activeStage?.dynoChart || {};

  const originalPower = cleanPoints(dyno?.original?.power, addZeroPoint);
  const originalTorque = cleanPoints(dyno?.original?.torque, addZeroPoint);
  const optimizedPower = cleanPoints(dyno?.optimized?.power, addZeroPoint);
  const optimizedTorque = cleanPoints(dyno?.optimized?.torque, addZeroPoint);

  const allPower = [...originalPower, ...optimizedPower];
  const allTorque = [...originalTorque, ...optimizedTorque];
  const allPoints = [...allPower, ...allTorque];

  const hasData = allPoints.length > 0;
  const originalHp = maxY(originalPower) || Number(apiData?.engine?.horsepower) || 0;
  const originalNm = maxY(originalTorque) || Number(apiData?.engine?.torque) || 0;
  const stageHp = maxY(optimizedPower) || Number(activeStage?.hp) || 0;
  const stageNm = maxY(optimizedTorque) || Number(activeStage?.nm) || 0;

  const rpmMax = maxX(allPoints) || fuel.defaultMaxRpm;
  const powerMax = roundUp(Math.max(originalHp, stageHp, Number(activeStage?.hp) || 0) + fuel.powerPad, 50);
  const torqueMax = roundUp(Math.max(originalNm, stageNm, Number(activeStage?.nm) || 0) + fuel.torquePad, 50);

  const chartData = {
    datasets: [
      {
        label: "Original PS",
        data: originalPower,
        yAxisID: "power",
        borderColor: "#9CA3AF",
        borderDash: [8, 5],
        borderWidth: 2,
        pointRadius: 0,
        pointHoverRadius: 4,
        tension: 0.42,
      },
      {
        label: `${activeStage?.name || "Stage"} PS`,
        data: optimizedPower,
        yAxisID: "power",
        borderColor: "#DC2626",
        borderWidth: 3,
        pointRadius: 0,
        pointHoverRadius: 4,
        tension: 0.42,
      },
      {
        label: "Original Nm",
        data: originalTorque,
        yAxisID: "torque",
        borderColor: "#A1A1AA",
        borderDash: [8, 5],
        borderWidth: 2,
        pointRadius: 0,
        pointHoverRadius: 4,
        tension: 0.42,
      },
      {
        label: `${activeStage?.name || "Stage"} Nm`,
        data: optimizedTorque,
        yAxisID: "torque",
        borderColor: "#3B82F6",
        borderWidth: 3,
        pointRadius: 0,
        pointHoverRadius: 4,
        tension: 0.42,
      },
    ].filter((dataset) => dataset.data.length > 0),
  };

  const options = {
    responsive: true,
    maintainAspectRatio: false,
    parsing: false,
    normalized: true,
    interaction: { mode: "nearest", intersect: false },
    plugins: {
      legend: {
        position: "top",
        align: "end",
        labels: {
          color: "#5F6470",
          boxWidth: 10,
          boxHeight: 10,
          usePointStyle: true,
          padding: 16,
          font: { size: 12, weight: "600" },
        },
      },
      tooltip: {
        backgroundColor: "#111827",
        titleColor: "#fff",
        bodyColor: "#F9FAFB",
        borderColor: "#D1D5DB",
        borderWidth: 1,
        callbacks: {
          title: (items) => items?.length ? `${Math.round(items[0].parsed.x)} rpm` : "",
          label: (item) => `${item.dataset.label}: ${Math.round(item.parsed.y)} ${item.dataset.yAxisID === "power" ? "PS" : "Nm"}`,
        },
      },
    },
    scales: {
      x: {
        type: "linear",
        min: 0,
        max: rpmMax,
        title: { display: true, text: "Drehzahl / rpm", color: "#6B7280", font: { size: 12, weight: "600" } },
        grid: { color: "rgba(17,24,39,.08)" },
        border: { color: "#DADDE3" },
        ticks: { color: "#6B7280", stepSize: fuel.rpmStep },
      },
      power: {
        type: "linear",
        position: "left",
        min: 0,
        max: powerMax,
        title: { display: true, text: "Leistung / PS", color: "#DC2626", font: { size: 12, weight: "700" } },
        grid: { color: "rgba(17,24,39,.08)" },
        border: { color: "#DADDE3" },
        ticks: { color: "#6B7280" },
      },
      torque: {
        type: "linear",
        position: "right",
        min: 0,
        max: torqueMax,
        title: { display: true, text: "Drehmoment / Nm", color: "#3B82F6", font: { size: 12, weight: "700" } },
        grid: { drawOnChartArea: false },
        border: { color: "#DADDE3" },
        ticks: { color: "#6B7280" },
      },
    },
  };

  if (!hasData) return <div className={`tfd-dyno tfd-dyno--empty ${className}`}>Keine Dyno-Chart-Daten vorhanden.</div>;

  return (
    <section className={`tfd-dyno ${className}`}>
      <header className="tfd-dyno__header">
        <div>
          <div className="tfd-dyno__eyebrow">DYNO-CHART</div>
          <h3>{title || buildTitle(apiData) || "Leistungsdiagramm"}</h3>
          <p>{subtitle || `${fuel.label} · ${activeStage?.name || "Stage"} · Original vs. optimiert`}</p>
        </div>

        <div className="tfd-dyno__summary">
          <div><span>Original</span><strong>{displayNumber(originalHp)} PS</strong></div>
          <div><span>{activeStage?.name || "Stage"}</span><strong>{displayNumber(stageHp)} PS</strong></div>
          <div><span>Original</span><strong>{displayNumber(originalNm)} Nm</strong></div>
          <div><span>{activeStage?.name || "Stage"}</span><strong>{displayNumber(stageNm)} Nm</strong></div>
        </div>
      </header>

      {showTabs && stages.length > 1 && (
        <div className="tfd-dyno__tabs" role="tablist" aria-label="Tuning-Stufen">
          {stages.map((item) => {
            const isActive = String(item.id) === String(activeStage?.id);
            return (
              <button type="button" key={item.id} className={isActive ? "is-active" : ""} onClick={() => setActiveStageId(item.id)}>
                <span>{item.name}</span>
                <strong>{item.hp || "-"} PS / {item.nm || "-"} Nm</strong>
              </button>
            );
          })}
        </div>
      )}

      <div className="tfd-dyno__chart" style={{ height }}>
        <Line key={String(activeStage?.id ?? "dyno-chart")} data={chartData} options={options} />
      </div>
    </section>
  );
}
