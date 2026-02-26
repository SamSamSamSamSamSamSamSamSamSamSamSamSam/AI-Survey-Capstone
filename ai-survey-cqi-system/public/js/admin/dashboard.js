// In Chart.js v3+, we must manually import and register the components 
// we need when using the CDN build.

// Only register components necessary for a Line Chart
const { Chart, LineController, CategoryScale, LinearScale, PointElement, LineElement, Tooltip, Legend } = window.Chart;

Chart.register(
    LineController, 
    CategoryScale, 
    LinearScale, 
    PointElement, 
    LineElement,
    Tooltip, 
    Legend
);

console.log('Labels:', dashboardData.monthlyLabels);
console.log('Ratings:', dashboardData.monthlyAvg);
console.log('PosPct:', dashboardData.monthlyPosPct);

// --- [NEW] Log data to console for debugging ---
console.log('Dashboard Chart Data:', dashboardData);

// --- Combined Monthly Performance Chart (Rating vs. Sentiment) ---
const ctxCombined = document.getElementById('monthlyCombinedChart')?.getContext('2d');

// Check if the canvas context exists before trying to create a chart
if (ctxCombined) {

    // Removed dynamic logic: chartType is now explicitly 'line'
    new Chart(ctxCombined, {
        type: 'line', // Explicitly set to Line Chart
        data: {
            labels: dashboardData.monthlyLabels,
            datasets: [
                {
                    label: 'Average Rating (1-5)',
                    data: dashboardData.monthlyAvg,
                    borderColor: '#198754', // Bootstrap Success Green
                    backgroundColor: 'rgba(25,135,84,0.5)', 
                    tension: 0.3,
                    fill: false,
                    yAxisID: 'y' // Link to the left Y-axis
                },
                {
                    label: 'Positive Sentiment % (0-100%)',
                    data: dashboardData.monthlyPosPct,
                    borderColor: '#0d6efd', // Bootstrap Primary Blue
                    backgroundColor: 'rgba(13,110,253,0.5)', 
                    tension: 0.3,
                    fill: false,
                    yAxisID: 'y1' // Link to the right Y-axis
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                tooltip: {
                    callbacks: {
                        // Custom tooltip label to add '%' to sentiment and format rating
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                // datasetIndex 0 is 'Average Rating', 1 is 'Positive Sentiment'
                                const isRating = context.datasetIndex === 0;
                                const value = context.parsed.y;
                                
                                label += isRating 
                                    ? value.toFixed(2) // 2 decimal places for rating
                                    : value.toFixed(1) + '%'; // 1 decimal place + % for sentiment
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                // Y-axis for Avg Rating (Left)
                y: {
                    type: 'linear', 
                    display: true,
                    position: 'left',
                    title: { display: true, text: 'Avg. Rating (1-5)' },
                    beginAtZero: true,
                    suggestedMax: 5
                },
                // Y1-axis for Sentiment Percentage (Right)
                y1: {
                    type: 'linear', 
                    display: true,
                    position: 'right',
                    title: { display: true, text: 'Positive Sentiment %' },
                    beginAtZero: true,
                    suggestedMax: 100,
                    // Don't draw grid lines for this axis to keep the chart clean
                    grid: { drawOnChartArea: false } 
                }
            }
        }
    });
} else {
    console.error('Canvas context not found for #monthlyCombinedChart.');
}