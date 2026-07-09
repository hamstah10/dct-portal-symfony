let powerChartInstance = null;
let peakMarkerRegistered = false;

export function initPowerCurveChart(config) {
    const {
        originalPs = 150,
        originalNm = 320,
        stagePs = 190,
        stageNm = 400,
        engineType = 'diesel',
        rpmStart: configuredRpmStart = 1200,
        rpmEnd: configuredRpmEnd = 7000,
        theme = 'default',
        stageLabel = 'Stage 1',
    } = config || {};

    const rpmStep = 100;

    const themes = {
        default: {
            text: '#3f4754',
            original: '#9aa4b2',
            originalPower: '#9aa4b2',
            originalTorque: '#9aa4b2',
            stage: '#ff5b52',
            stagePower: '#ff5b52',
            stageTorque: '#ff5b52',
            stageFill: '#ffc94d',
            grid: 'rgba(88, 100, 83, 0.34)',
            tooltipBg: 'rgba(20, 24, 32, 0.96)',
            tooltipText: '#e8edf4',
            markerText: '#f8fafc',
        },
        catalogLight: {
            text: '#687184',
            original: '#171d2a',
            originalPower: '#171d2a',
            originalTorque: '#171d2a',
            stage: '#c4933c',
            stagePower: '#c4933c',
            stageTorque: '#c4933c',
            stageFill: '#c4933c',
            grid: '#d9dee7',
            tooltipBg: 'rgba(23, 29, 42, 0.96)',
            tooltipText: '#ffffff',
            markerText: '#171d2a',
        },
    };

    const numeric = (value, fallback = 0) => {
        const parsed = Number(value);
        return Number.isFinite(parsed) ? parsed : fallback;
    };

    const rpmStart = Math.max(0, numeric(configuredRpmStart, 1200));
    const rpmEnd = Math.max(rpmStart + 500, numeric(configuredRpmEnd, 7000));
    const currentTheme = themes[theme] || themes.default;

    function rpmToPs(nm, rpm) {
        return +((nm * rpm) / 7023).toFixed(1);
    }

    function createRpmArray(start, end, step) {
        const values = [];
        for (let rpm = start; rpm <= end; rpm += step) {
            values.push(rpm);
        }
        return values;
    }

    function clamp(value, min, max) {
        return Math.max(min, Math.min(max, value));
    }

    function smoothstep(edge0, edge1, x) {
        const t = clamp((x - edge0) / (edge1 - edge0), 0, 1);
        return t * t * (3 - 2 * t);
    }

    function generateTorqueCurve(maxNm, rpmArray, type, variant = 'stock') {
        const min = rpmArray[0];
        const max = rpmArray[rpmArray.length - 1];
        const span = max - min;

        let riseStart;
        let riseEnd;
        let plateauEnd;
        let endFactor;
        let lowFactor;

        if (type === 'diesel') {
            riseStart = min + span * 0.03;
            riseEnd = min + span * 0.23;
            plateauEnd = min + span * 0.56;
            lowFactor = variant === 'stage' ? 0.66 : 0.58;
            endFactor = variant === 'stage' ? 0.42 : 0.36;
        } else {
            riseStart = min + span * 0.08;
            riseEnd = min + span * 0.34;
            plateauEnd = min + span * 0.68;
            lowFactor = variant === 'stage' ? 0.58 : 0.50;
            endFactor = variant === 'stage' ? 0.50 : 0.45;
        }

        return rpmArray.map((rpm) => {
            let value;

            if (rpm <= riseEnd) {
                const t = smoothstep(riseStart, riseEnd, rpm);
                value = maxNm * (lowFactor + (1 - lowFactor) * t);
            } else if (rpm <= plateauEnd) {
                const t = smoothstep(riseEnd, plateauEnd, rpm);
                const drop = type === 'diesel' ? 0.07 : 0.04;
                value = maxNm * (1 - drop * t);
            } else {
                const t = smoothstep(plateauEnd, max, rpm);
                const startFactor = type === 'diesel' ? 0.93 : 0.96;
                value = maxNm * (startFactor - (startFactor - endFactor) * t);
            }

            return +value.toFixed(1);
        });
    }

    function fitCurveToTargetPs(torqueCurve, rpmArray, targetPs, type) {
        const currentPs = torqueCurve.map((nm, index) => rpmToPs(nm, rpmArray[index]));
        const peak = Math.max(...currentPs);
        if (!Number.isFinite(peak) || peak <= 0) {
            return torqueCurve;
        }

        const factor = targetPs / peak;

        return torqueCurve.map((nm, index) => {
            const rpm = rpmArray[index];
            const bias = type === 'diesel'
                ? 0.80 + 0.22 * smoothstep(rpmArray[0], rpmArray[rpmArray.length - 1], rpm)
                : 0.84 + 0.16 * smoothstep(rpmArray[0], rpmArray[rpmArray.length - 1], rpm);

            return +(nm * (1 + (factor - 1) * bias)).toFixed(1);
        });
    }

    function findPeak(values, labels) {
        const value = Math.max(...values);
        const index = values.indexOf(value);
        return {
            value,
            index,
            rpm: labels[index],
        };
    }

    if (typeof Chart === 'undefined') {
        return;
    }

    const peakMarkerPlugin = {
        id: 'peakMarkerPlugin',
        afterDatasetsDraw(chart, _args, options) {
            if (!options || !options.enabled) {
                return;
            }

            const { ctx } = chart;
            (options.markers || []).forEach((marker) => {
                const meta = chart.getDatasetMeta(marker.datasetIndex);
                if (!meta || !meta.data || !meta.data[marker.index]) {
                    return;
                }

                const point = meta.data[marker.index];
                const x = point.x;
                const y = point.y;

                ctx.save();
                ctx.beginPath();
                ctx.arc(x, y, 5, 0, Math.PI * 2);
                ctx.fillStyle = marker.color;
                ctx.fill();
                ctx.lineWidth = 2;
                ctx.strokeStyle = '#ffffff';
                ctx.stroke();

                ctx.fillStyle = currentTheme.markerText;
                ctx.font = 'bold 12px Arial';
                ctx.fillText(marker.label, x + 10, y - 10);
                ctx.restore();
            });
        },
    };

    if (!peakMarkerRegistered) {
        const alreadyRegistered = Boolean(Chart.registry?.plugins?.get?.('peakMarkerPlugin'));
        if (!alreadyRegistered) {
            Chart.register(peakMarkerPlugin);
        }
        peakMarkerRegistered = true;
    }

    const canvas = document.getElementById('powerChart');
    if (!canvas) {
        return;
    }

    const ctx = canvas.getContext('2d');
    if (!ctx) {
        return;
    }

    const rpmArray = createRpmArray(rpmStart, rpmEnd, rpmStep);
    const type = String(engineType || '')
        .trim()
        .toLowerCase()
        .includes('diesel')
        ? 'diesel'
        : 'petrol';

    const stockPs = numeric(originalPs, 0);
    const stockNm = numeric(originalNm, 0);
    const tunedPs = numeric(stagePs, 0);
    const tunedNm = numeric(stageNm, 0);

    let stockTorque = generateTorqueCurve(stockNm, rpmArray, type, 'stock');
    let tunedTorque = generateTorqueCurve(tunedNm, rpmArray, type, 'stage');

    stockTorque = fitCurveToTargetPs(stockTorque, rpmArray, stockPs, type);
    tunedTorque = fitCurveToTargetPs(tunedTorque, rpmArray, tunedPs, type);

    const stockPower = stockTorque.map((nm, index) => rpmToPs(nm, rpmArray[index]));
    const tunedPower = tunedTorque.map((nm, index) => rpmToPs(nm, rpmArray[index]));

    const peakNm = findPeak(tunedTorque, rpmArray);
    const peakPs = findPeak(tunedPower, rpmArray);

    const deltaPsNode = document.getElementById('chartDeltaPs') || document.getElementById('deltaPs');
    const deltaNmNode = document.getElementById('chartDeltaNm') || document.getElementById('deltaNm');
    const peakPsNode = document.getElementById('peakPs');
    const peakPsNoteNode = document.getElementById('peakPsNote');
    const peakNmNode = document.getElementById('peakNm');
    const peakNmNoteNode = document.getElementById('peakNmNote');

    const formatSigned = (value, unit) => `${value >= 0 ? '+' : ''}${value} ${unit}`;
    const formatPeakValue = (value) => Number.isInteger(value) ? String(value) : value.toFixed(1);

    if (deltaPsNode) {
        deltaPsNode.textContent = formatSigned(Math.round(tunedPs - stockPs), 'PS');
    }
    if (deltaNmNode) {
        deltaNmNode.textContent = formatSigned(Math.round(tunedNm - stockNm), 'Nm');
    }
    if (peakPsNode) {
        peakPsNode.textContent = `${formatPeakValue(peakPs.value)} PS`;
    }
    if (peakPsNoteNode) {
        peakPsNoteNode.textContent = `bei ${peakPs.rpm} U/min`;
    }
    if (peakNmNode) {
        peakNmNode.textContent = `${formatPeakValue(peakNm.value)} Nm`;
    }
    if (peakNmNoteNode) {
        peakNmNoteNode.textContent = `bei ${peakNm.rpm} U/min`;
    }

    if (powerChartInstance) {
        powerChartInstance.destroy();
        powerChartInstance = null;
    }

    powerChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: rpmArray,
            datasets: [
                {
                    label: theme === 'catalogLight' ? 'OEM Torque Nm' : 'Nm Serie',
                    data: stockTorque,
                    borderColor: currentTheme.originalTorque,
                    backgroundColor: 'transparent',
                    borderDash: theme === 'catalogLight' ? [7, 5] : [],
                    borderWidth: 3,
                    tension: 0.34,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    yAxisID: 'yNm',
                },
                {
                    label: theme === 'catalogLight' ? `${stageLabel} Torque Nm` : 'Nm Stage 1',
                    data: tunedTorque,
                    borderColor: currentTheme.stageTorque,
                    backgroundColor: 'transparent',
                    borderWidth: 4,
                    tension: 0.34,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    fill: false,
                    yAxisID: 'yNm',
                },
                {
                    label: theme === 'catalogLight' ? 'OEM Power HP' : 'PS Serie',
                    data: stockPower,
                    borderColor: currentTheme.originalPower,
                    backgroundColor: 'transparent',
                    borderDash: [7, 5],
                    borderWidth: 3,
                    tension: 0.34,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    fill: false,
                    yAxisID: 'yPs',
                },
                {
                    label: theme === 'catalogLight' ? `${stageLabel} Power HP` : 'PS Stage 1',
                    data: tunedPower,
                    borderColor: currentTheme.stagePower,
                    backgroundColor: 'transparent',
                    borderDash: [7, 5],
                    borderWidth: 3,
                    tension: 0.34,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    fill: false,
                    yAxisID: 'yPs',
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 1100,
                easing: 'easeOutQuart',
            },
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    align: 'end',
                    labels: {
                        color: currentTheme.text,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        boxWidth: 10,
                        boxHeight: 10,
                        padding: 18,
                        font: {
                            size: 14,
                            weight: '600',
                        },
                    },
                },
                tooltip: {
                    backgroundColor: currentTheme.tooltipBg,
                    titleColor: currentTheme.tooltipText,
                    bodyColor: currentTheme.tooltipText,
                    borderColor: currentTheme.grid,
                    borderWidth: 1,
                    padding: 12,
                    callbacks: {
                        title(items) {
                            return `${items[0].label} U/min`;
                        },
                    },
                },
                peakMarkerPlugin: {
                    enabled: true,
                    markers: [
                        {
                            datasetIndex: 1,
                            index: peakNm.index,
                            color: currentTheme.stage,
                            label: `${formatPeakValue(peakNm.value)} Nm`,
                        },
                        {
                            datasetIndex: 3,
                            index: peakPs.index,
                            color: currentTheme.stage,
                            label: `${formatPeakValue(peakPs.value)} PS`,
                        },
                    ],
                },
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Drehzahl (U/min)',
                        color: currentTheme.text,
                        font: {
                            weight: 'bold',
                        },
                    },
                    ticks: {
                        color: currentTheme.text,
                        maxTicksLimit: 12,
                    },
                    grid: {
                        color: currentTheme.grid,
                        lineWidth: 1.1,
                    },
                },
                yNm: {
                    type: 'linear',
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Drehmoment (Nm)',
                        color: currentTheme.text,
                        font: {
                            weight: 'bold',
                        },
                    },
                    ticks: {
                        color: currentTheme.text,
                    },
                    grid: {
                        color: currentTheme.grid,
                        lineWidth: 1.1,
                    },
                },
                yPs: {
                    type: 'linear',
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Leistung (PS)',
                        color: currentTheme.text,
                        font: {
                            weight: 'bold',
                        },
                    },
                    ticks: {
                        color: currentTheme.text,
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                },
            },
        },
    });
}
