// Monthly rating line
    const { monthlyLabels, monthlyAvg, monthlyPosPct } = window.dashboardData;

    const ctx1 = document.getElementById('monthlyRatingChart').getContext('2d');
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: monthlyLabels,
            datasets: [{
                label: 'Average Rating',
                data: monthlyAvg,
                borderColor: '#198754',
                backgroundColor: 'rgba(25,135,84,0.06)',
                tension: 0.2,
                fill: true
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true, suggestedMax: 5 } } }
    });

    // Monthly positive percent
    const ctx2 = document.getElementById('monthlySentimentChart').getContext('2d');
    new Chart(ctx2, {
        type: 'line',
        data: {
            labels: monthlyLabels,
            datasets: [{
                label: 'Positive %',
                data: monthlyPosPct,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13,110,253,0.06)',
                tension: 0.2,
                fill: true
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true, suggestedMax: 100 } } }
    });