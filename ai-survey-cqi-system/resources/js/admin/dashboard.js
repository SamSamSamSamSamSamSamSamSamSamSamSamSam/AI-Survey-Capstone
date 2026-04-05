import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

// =============================================================================
// dashboard.js
// Admin dashboard — filter handlers + all chart initializations
//
// Charts:
//  1. monthlyCombinedChart    — Bar+Line: avg rating + positive sentiment %
//  2. sentimentDonutChart     — Doughnut: positive / neutral / negative split
//  3. ratingDistributionChart — Bar: response count per score 1–5
//  4. facultyComparisonChart  — Horizontal bar: top 10 faculty avg ratings
//  5. categoryRadarChart      — Radar: avg score per evaluation category
// =============================================================================

// ── Shared palette ────────────────────────────────────────────────────────────
const COLOR = {
    primary:  'rgba(78, 115, 223',   // matches your existing blue
    success:  'rgba(28, 200, 138',   // matches your existing green
    warning:  'rgba(245, 158, 11',
    danger:   'rgba(239, 68, 68',
    neutral:  'rgba(148, 163, 184',
    gridLine: 'rgba(0, 0, 0, 0.05)',
    target:   'rgba(239, 68, 68, 0.65)',
};

// Append opacity to a COLOR value: rgba(r,g,b  →  rgba(r,g,b,a)
const alpha = (base, a) => `${base}, ${a})`;

// ── Utility: empty-state inside canvas parent ─────────────────────────────────
function showEmpty(canvas, message) {
    canvas.style.display = 'none';
    const el = document.createElement('div');
    el.className = 'dash-empty';
    el.innerHTML = `<i class="bi bi-bar-chart dash-empty__icon"></i><span>${message}</span>`;
    canvas.parentElement.appendChild(el);
}

function isEmpty(arr) {
    return !arr || arr.length === 0 || arr.every(v => v === 0 || v === null);
}

// =============================================================================
document.addEventListener('DOMContentLoaded', () => {

    // ── Filter selects — redirect on change ───────────────────────────────────
    ['survey-filter', 'course-filter', 'teacher-filter', 'semester-filter'].forEach(id => {
        document.getElementById(id)?.addEventListener('change', function () {
            window.location.href = this.value;
        });
    });

    if (typeof dashboardData === 'undefined') return;

    const {
        monthlyLabels, monthlyAvg, monthlyPosPct,
        sentimentTotals,
        ratingDistribution,
        facultyNames, facultyRatings,
        categoryLabels, categoryAvgs,
    } = dashboardData;

    // ── 1. Monthly Combined Chart (your original, + target reference line) ────
    const monthlyCanvas = document.getElementById('monthlyCombinedChart');
    if (monthlyCanvas) {
        const targetLine = (monthlyLabels ?? []).map(() => 4.0);

        new Chart(monthlyCanvas, {
            type: 'bar',
            data: {
                labels: monthlyLabels,
                datasets: [
                    {
                        type: 'bar',
                        label: 'Mean Rating',
                        data: monthlyAvg,
                        backgroundColor: alpha(COLOR.primary, 0.2),
                        borderColor:     alpha(COLOR.primary, 0.8),
                        borderWidth: 1.5,
                        borderRadius: 4,
                        yAxisID: 'yRating',
                        order: 3,
                    },
                    {
                        type: 'line',
                        label: 'Positive Sentiment %',
                        data: monthlyPosPct,
                        borderColor:          alpha(COLOR.success, 0.9),
                        backgroundColor:      alpha(COLOR.success, 0.08),
                        borderWidth: 2,
                        pointBackgroundColor: alpha(COLOR.success, 1),
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.4,
                        yAxisID: 'ySentiment',
                        order: 2,
                    },
                    {
                        type: 'line',
                        label: 'Target (4.0)',
                        data: targetLine,
                        yAxisID: 'yRating',
                        borderColor: COLOR.target,
                        borderWidth: 1.5,
                        borderDash: [6, 4],
                        pointRadius: 0,
                        pointHoverRadius: 0,
                        fill: false,
                        tension: 0,
                        order: 1,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            pointStyleWidth: 10,
                            font: { size: 12 },
                            color: '#6c757d',
                        },
                    },
                    tooltip: {
                        backgroundColor: '#fff',
                        borderColor: 'rgba(0,0,0,0.1)',
                        borderWidth: 1,
                        titleColor: '#333',
                        bodyColor: '#555',
                        padding: 10,
                        callbacks: {
                            label(ctx) {
                                if (ctx.dataset.label === 'Target (4.0)') return null;
                                const label = ctx.dataset.label ?? '';
                                const value = ctx.parsed.y ?? 0;
                                return ctx.datasetIndex === 0
                                    ? ` ${label}: ${value.toFixed(2)}`
                                    : ` ${label}: ${value}%`;
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#6c757d', font: { size: 11 } },
                    },
                    yRating: {
                        type: 'linear',
                        position: 'left',
                        min: 0,
                        max: 5,
                        ticks: { stepSize: 1, color: '#6c757d', font: { size: 11 } },
                        grid: { color: COLOR.gridLine },
                        title: { display: true, text: 'Mean Rating', color: '#6c757d', font: { size: 11 } },
                    },
                    ySentiment: {
                        type: 'linear',
                        position: 'right',
                        min: 0,
                        max: 100,
                        ticks: { color: '#6c757d', font: { size: 11 }, callback: val => `${val}%` },
                        grid: { drawOnChartArea: false },
                        title: { display: true, text: 'Positive Sentiment %', color: '#6c757d', font: { size: 11 } },
                    },
                },
            },
        });
    }

    // ── 2. Sentiment Donut ────────────────────────────────────────────────────
    const donutCanvas = document.getElementById('sentimentDonutChart');
    if (donutCanvas) {
        const { positive = 0, neutral = 0, negative = 0 } = sentimentTotals ?? {};
        const total = positive + neutral + negative;

        if (total === 0) {
            showEmpty(donutCanvas, 'No sentiment data yet.');
        } else {
            new Chart(donutCanvas, {
                type: 'doughnut',
                data: {
                    labels: ['Positive', 'Neutral', 'Negative'],
                    datasets: [{
                        data: [positive, neutral, negative],
                        backgroundColor: [
                            alpha(COLOR.success, 0.8),
                            alpha(COLOR.warning, 0.8),
                            alpha(COLOR.danger,  0.8),
                        ],
                        borderColor: ['#fff', '#fff', '#fff'],
                        borderWidth: 2,
                        hoverOffset: 6,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    cutout: '68%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#fff',
                            borderColor: 'rgba(0,0,0,0.1)',
                            borderWidth: 1,
                            titleColor: '#333',
                            bodyColor: '#555',
                            padding: 10,
                            callbacks: {
                                label: ctx => {
                                    const pct = ((ctx.parsed / total) * 100).toFixed(1);
                                    return ` ${ctx.label}: ${ctx.parsed} (${pct}%)`;
                                },
                            },
                        },
                    },
                },
                plugins: [{
                    id: 'centreText',
                    afterDraw(chart) {
                        const { ctx: c, chartArea: { left, top, right, bottom } } = chart;
                        const cx = (left + right) / 2;
                        const cy = (top + bottom) / 2;
                        c.save();
                        c.textAlign    = 'center';
                        c.textBaseline = 'middle';
                        c.font         = '700 22px "Segoe UI", system-ui, sans-serif';
                        c.fillStyle    = '#1e293b';
                        c.fillText(total, cx, cy - 8);
                        c.font      = '400 11px "Segoe UI", system-ui, sans-serif';
                        c.fillStyle = '#94a3b8';
                        c.fillText('responses', cx, cy + 12);
                        c.restore();
                    },
                }],
            });

            // Build custom legend
            const legend = document.getElementById('sentimentLegend');
            if (legend) {
                const colours = [
                    alpha(COLOR.success, 1),
                    alpha(COLOR.warning, 1),
                    alpha(COLOR.danger,  1),
                ];
                [['Positive', positive], ['Neutral', neutral], ['Negative', negative]]
                    .forEach(([label, count], i) => {
                        const pct = ((count / total) * 100).toFixed(1);
                        legend.innerHTML += `
                            <div class="donut-legend__item">
                                <span class="donut-legend__dot" style="background:${colours[i]}"></span>
                                <span class="donut-legend__label">${label}</span>
                                <span class="donut-legend__pct">${pct}%</span>
                            </div>`;
                    });
            }
        }
    }

    // ── 3. Rating Distribution ────────────────────────────────────────────────
    const distCanvas = document.getElementById('ratingDistributionChart');
    if (distCanvas) {
        const counts = ratingDistribution ?? [];

        if (isEmpty(counts)) {
            showEmpty(distCanvas, 'No rating data yet.');
        } else {
            const barColors = counts.map((_, i) =>
                i < 2 ? alpha(COLOR.danger,  0.75) :
                i < 3 ? alpha(COLOR.warning, 0.75) :
                        alpha(COLOR.success, 0.75)
            );
            const borderColors = counts.map((_, i) =>
                i < 2 ? alpha(COLOR.danger,  1) :
                i < 3 ? alpha(COLOR.warning, 1) :
                        alpha(COLOR.success, 1)
            );

            new Chart(distCanvas, {
                type: 'bar',
                data: {
                    labels: ['Score 1', 'Score 2', 'Score 3', 'Score 4', 'Score 5'],
                    datasets: [{
                        label: 'Responses',
                        data: counts,
                        backgroundColor: barColors,
                        borderColor: borderColors,
                        borderWidth: 1.5,
                        borderRadius: 6,
                        borderSkipped: false,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#fff',
                            borderColor: 'rgba(0,0,0,0.1)',
                            borderWidth: 1,
                            titleColor: '#333',
                            bodyColor: '#555',
                            padding: 10,
                            callbacks: {
                                label: ctx => ` ${ctx.parsed.y} responses`,
                            },
                        },
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: '#6c757d', font: { size: 11 } },
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: COLOR.gridLine },
                            ticks: { stepSize: 1, precision: 0, color: '#6c757d', font: { size: 11 } },
                            title: { display: true, text: 'Count', color: '#6c757d', font: { size: 11 } },
                        },
                    },
                },
            });
        }
    }

    // ── 4. Faculty Comparison (Horizontal Bar) ────────────────────────────────
    const facultyCanvas = document.getElementById('facultyComparisonChart');
    if (facultyCanvas) {
        const names   = facultyNames   ?? [];
        const ratings = facultyRatings ?? [];

        if (isEmpty(names)) {
            showEmpty(facultyCanvas, 'No faculty data yet.');
        } else {
            const barColors = ratings.map(r =>
                r >= 4.5 ? alpha(COLOR.success, 0.75) :
                r >= 4.0 ? alpha(COLOR.primary, 0.75) :
                r >= 3.0 ? alpha(COLOR.warning, 0.75) :
                           alpha(COLOR.danger,  0.75)
            );

            new Chart(facultyCanvas, {
                type: 'bar',
                data: {
                    labels: names,
                    datasets: [
                        {
                            label: 'Avg Rating',
                            data: ratings,
                            backgroundColor: barColors,
                            borderColor: barColors.map(c => c.replace(/,\s*[\d.]+\)$/, ', 1)')),
                            borderWidth: 1,
                            borderRadius: 5,
                            borderSkipped: false,
                            order: 2,
                        },
                        {
                            label: 'Target (4.0)',
                            data: names.map(() => 4.0),
                            type: 'line',
                            borderColor: COLOR.target,
                            borderWidth: 1.5,
                            borderDash: [6, 4],
                            pointRadius: 0,
                            fill: false,
                            order: 1,
                        },
                    ],
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            align: 'end',
                            labels: { boxWidth: 12, boxHeight: 12, padding: 12, color: '#6c757d', font: { size: 12 } },
                        },
                        tooltip: {
                            backgroundColor: '#fff',
                            borderColor: 'rgba(0,0,0,0.1)',
                            borderWidth: 1,
                            titleColor: '#333',
                            bodyColor: '#555',
                            padding: 10,
                            callbacks: {
                                label: ctx => {
                                    if (ctx.dataset.label === 'Target (4.0)') return null;
                                    return ` Avg Rating: ${ctx.parsed.x}`;
                                },
                            },
                        },
                    },
                    scales: {
                        x: {
                            min: 0,
                            max: 5,
                            grid: { color: COLOR.gridLine },
                            ticks: { callback: v => v.toFixed(1), color: '#6c757d', font: { size: 11 } },
                        },
                        y: {
                            grid: { display: false },
                            ticks: { color: '#6c757d', font: { size: 11 } },
                        },
                    },
                },
            });
        }
    }

    // ── 5. Category Radar ─────────────────────────────────────────────────────
    const radarCanvas = document.getElementById('categoryRadarChart');
    if (radarCanvas) {
        const labels = categoryLabels ?? [];
        const avgs   = categoryAvgs   ?? [];

        if (isEmpty(labels)) {
            showEmpty(radarCanvas, 'No category data yet.');
        } else {
            new Chart(radarCanvas, {
                type: 'radar',
                data: {
                    labels,
                    datasets: [
                        {
                            label: 'Avg Score',
                            data: avgs,
                            backgroundColor: alpha(COLOR.primary, 0.15),
                            borderColor:     alpha(COLOR.primary, 0.8),
                            borderWidth: 2,
                            pointBackgroundColor: alpha(COLOR.primary, 1),
                            pointRadius: 4,
                            pointHoverRadius: 6,
                        },
                        {
                            label: 'Target (4.0)',
                            data: labels.map(() => 4.0),
                            backgroundColor: 'transparent',
                            borderColor: COLOR.target,
                            borderWidth: 1.5,
                            borderDash: [5, 4],
                            pointRadius: 0,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            align: 'end',
                            labels: { boxWidth: 12, boxHeight: 12, padding: 12, color: '#6c757d', font: { size: 12 } },
                        },
                        tooltip: {
                            backgroundColor: '#fff',
                            borderColor: 'rgba(0,0,0,0.1)',
                            borderWidth: 1,
                            titleColor: '#333',
                            bodyColor: '#555',
                            padding: 10,
                        },
                    },
                    scales: {
                        r: {
                            min: 0,
                            max: 5,
                            ticks: {
                                stepSize: 1,
                                backdropColor: 'transparent',
                                color: '#6c757d',
                                font: { size: 10 },
                            },
                            grid:        { color: COLOR.gridLine },
                            angleLines:  { color: COLOR.gridLine },
                            pointLabels: { color: '#6c757d', font: { size: 11 } },
                        },
                    },
                },
            });
        }
    }

}); // end DOMContentLoaded