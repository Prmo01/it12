@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function createGradient(ctx, color1, color2) {
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, color1);
        gradient.addColorStop(1, color2);
        return gradient;
    }

    // Project Status Chart
    const projectStatusChartEl = document.getElementById('projectStatusChart');
    if (projectStatusChartEl) {
        const projectCtx = projectStatusChartEl.getContext('2d');
        new Chart(projectCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode(array_keys($projectStatusData->toArray())) !!},
                datasets: [{
                    data: {!! json_encode(array_values($projectStatusData->toArray())) !!},
                    backgroundColor: ['#2563eb', '#10b981', '#f59e0b', '#ef4444', '#6b7280'],
                    borderWidth: 3,
                    borderColor: '#ffffff',
                    hoverOffset: 12
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { animateRotate: true, duration: 1500 },
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 20, usePointStyle: true } },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.85)',
                        padding: 14,
                        cornerRadius: 10,
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed / total) * 100).toFixed(1);
                                return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // Monthly Projects Trend Chart
    const monthlyProjectsChartEl = document.getElementById('monthlyProjectsChart');
    if (monthlyProjectsChartEl) {
        const monthlyProjectsCtx = monthlyProjectsChartEl.getContext('2d');
        const monthlyProjectsGradient = createGradient(monthlyProjectsCtx, 'rgba(37, 99, 235, 0.3)', 'rgba(37, 99, 235, 0.05)');
        const monthlyProjectsLabels = {!! json_encode(array_keys($monthlyProjects->toArray())) !!};
        const monthlyProjectsData = {!! json_encode(array_values($monthlyProjects->toArray())) !!};
        
        new Chart(monthlyProjectsCtx, {
            type: 'line',
            data: {
                labels: monthlyProjectsLabels.map(month => {
                    const date = new Date(month + '-01');
                    return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                }),
                datasets: [{
                    label: 'Projects',
                    data: monthlyProjectsData,
                    borderColor: '#2563eb',
                    backgroundColor: monthlyProjectsGradient,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 2000 },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.85)',
                        padding: 14,
                        cornerRadius: 10
                    }
                },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#e5e7eb' }, ticks: { stepSize: 1 } },
                    x: { grid: { display: false } }
                }
            }
        });
    }
</script>
@endpush

