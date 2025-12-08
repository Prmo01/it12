@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function createGradient(ctx, color1, color2) {
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, color1);
        gradient.addColorStop(1, color2);
        return gradient;
    }

    // PO Status Chart
    const poStatusChartEl = document.getElementById('poStatusChart');
    if (poStatusChartEl) {
        const poCtx = poStatusChartEl.getContext('2d');
        const poLabels = {!! json_encode(array_keys($poStatusData->toArray())) !!};
        const poData = {!! json_encode(array_values($poStatusData->toArray())) !!};
        
        new Chart(poCtx, {
            type: 'bar',
            data: {
                labels: poLabels,
                datasets: [{
                    label: 'Purchase Orders',
                    data: poData,
                    backgroundColor: '#2563eb',
                    borderColor: '#2563eb',
                    borderWidth: 2,
                    borderRadius: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 1500 },
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

    // PR Status Chart
    const prStatusChartEl = document.getElementById('prStatusChart');
    if (prStatusChartEl) {
        const prCtx = prStatusChartEl.getContext('2d');
        const prLabels = {!! json_encode(array_keys($prStatusData->toArray())) !!};
        const prData = {!! json_encode(array_values($prStatusData->toArray())) !!};
        
        new Chart(prCtx, {
            type: 'doughnut',
            data: {
                labels: prLabels,
                datasets: [{
                    data: prData,
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
                        cornerRadius: 10
                    }
                }
            }
        });
    }

    // Monthly PO Trend Chart
    const monthlyPOChartEl = document.getElementById('monthlyPOChart');
    if (monthlyPOChartEl) {
        const monthlyPOCtx = monthlyPOChartEl.getContext('2d');
        const monthlyPOGradient = createGradient(monthlyPOCtx, 'rgba(16, 185, 129, 0.3)', 'rgba(16, 185, 129, 0.05)');
        const monthlyPOLabels = {!! json_encode(array_keys($monthlyPOs->toArray())) !!};
        const monthlyPOData = {!! json_encode(array_values($monthlyPOs->toArray())) !!};
        
        new Chart(monthlyPOCtx, {
            type: 'line',
            data: {
                labels: monthlyPOLabels.map(month => {
                    const date = new Date(month + '-01');
                    return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                }),
                datasets: [{
                    label: 'Purchase Orders',
                    data: monthlyPOData,
                    borderColor: '#10b981',
                    backgroundColor: monthlyPOGradient,
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

    // Top Suppliers Chart
    const topSuppliersChartEl = document.getElementById('topSuppliersChart');
    if (topSuppliersChartEl) {
        const suppliersCtx = topSuppliersChartEl.getContext('2d');
        const suppliersData = {!! json_encode($topSuppliers) !!};
        
        const supplierNames = suppliersData.map(s => s.supplier ? s.supplier.name : 'Unknown');
        const supplierOrders = suppliersData.map(s => s.order_count);
        
        new Chart(suppliersCtx, {
            type: 'bar',
            data: {
                labels: supplierNames,
                datasets: [{
                    label: 'Orders',
                    data: supplierOrders,
                    backgroundColor: '#f59e0b',
                    borderColor: '#f59e0b',
                    borderWidth: 2,
                    borderRadius: 8
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 1500 },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.85)',
                        padding: 14,
                        cornerRadius: 10,
                        callbacks: {
                            afterLabel: function(context) {
                                const supplier = suppliersData[context.dataIndex];
                                return 'Total: ₱' + parseFloat(supplier.total_amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            }
                        }
                    }
                },
                scales: {
                    x: { beginAtZero: true, grid: { color: '#e5e7eb' }, ticks: { stepSize: 1 } },
                    y: { grid: { display: false } }
                }
            }
        });
    }
</script>
@endpush

