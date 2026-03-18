import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

// =============================================================================
// dashboard.js
// Admin dashboard — filter handlers + chart initialization
// =============================================================================

document.addEventListener('DOMContentLoaded', () => {

    // -------------------------------------------------------------------------
    // Filter selects — redirect on change
    // -------------------------------------------------------------------------
    ['survey-filter', 'course-filter'].forEach(id => {
        document.getElementById(id)?.addEventListener('change', function () {
            window.location.href = this.value;
        });
    });

    // -------------------------------------------------------------------------
    // Monthly Combined Chart
    // dashboardData is injected inline by the Blade view:
    //   { monthlyLabels, monthlyAvg, monthlyPosPct }
    // -------------------------------------------------------------------------
    const canvas = document.getElementById('monthlyCombinedChart');

    if (!canvas || typeof dashboardData === 'undefined') return;

    const { monthlyLabels, monthlyAvg, monthlyPosPct } = dashboardData;

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: monthlyLabels,
            datasets: [
                {
                    // Bar dataset — Mean Rating (left axis)
                    type: 'bar',
                    label: 'Mean Rating',
                    data: monthlyAvg,
                    backgroundColor: 'rgba(78, 115, 223, 0.2)',
                    borderColor: 'rgba(78, 115, 223, 0.8)',
                    borderWidth: 1.5,
                    borderRadius: 4,
                    yAxisID: 'yRating',
                    order: 2,
                },
                {
                    // Line dataset — Positive Sentiment % (right axis)
                    type: 'line',
                    label: 'Positive Sentiment %',
                    data: monthlyPosPct,
                    borderColor: 'rgba(28, 200, 138, 0.9)',
                    backgroundColor: 'rgba(28, 200, 138, 0.08)',
                    borderWidth: 2,
                    pointBackgroundColor: 'rgba(28, 200, 138, 1)',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'ySentiment',
                    order: 1,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
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
                    ticks: {
                        stepSize: 1,
                        color: '#6c757d',
                        font: { size: 11 },
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.05)',
                    },
                    title: {
                        display: true,
                        text: 'Mean Rating',
                        color: '#6c757d',
                        font: { size: 11 },
                    },
                },
                ySentiment: {
                    type: 'linear',
                    position: 'right',
                    min: 0,
                    max: 100,
                    ticks: {
                        color: '#6c757d',
                        font: { size: 11 },
                        callback: val => `${val}%`,
                    },
                    grid: { drawOnChartArea: false },
                    title: {
                        display: true,
                        text: 'Positive Sentiment %',
                        color: '#6c757d',
                        font: { size: 11 },
                    },
                },
            },
        },
    });

});