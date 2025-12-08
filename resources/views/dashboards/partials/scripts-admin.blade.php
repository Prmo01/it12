@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Helper function to create gradient
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

    // Inventory Movements Chart
    const inventoryMovementChartEl = document.getElementById('inventoryMovementChart');
    if (inventoryMovementChartEl) {
        const inventoryCtx = inventoryMovementChartEl.getContext('2d');
        const inventoryData = {!! json_encode($inventoryMovements) !!};
        
        const inventoryDates = Object.keys(inventoryData);
        const inData = inventoryDates.map(date => {
            const dayData = inventoryData[date];
            const inMovement = dayData.find(m => m.movement_type === 'in' || m.movement_type.includes('in'));
            return inMovement ? parseFloat(inMovement.total) : 0;
        });
        const outData = inventoryDates.map(date => {
            const dayData = inventoryData[date];
            const outMovement = dayData.find(m => m.movement_type === 'out' || m.movement_type.includes('out'));
            return outMovement ? parseFloat(outMovement.total) : 0;
        });
        
        new Chart(inventoryCtx, {
            type: 'bar',
            data: {
                labels: inventoryDates.map(date => {
                    const d = new Date(date);
                    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                }),
                datasets: [{
                    label: 'Stock In',
                    data: inData,
                    backgroundColor: 'rgba(16, 185, 129, 0.8)',
                    borderColor: '#10b981',
                    borderWidth: 2,
                    borderRadius: 6
                }, {
                    label: 'Stock Out',
                    data: outData,
                    backgroundColor: 'rgba(239, 68, 68, 0.8)',
                    borderColor: '#ef4444',
                    borderWidth: 2,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 1500 },
                plugins: {
                    legend: { position: 'top', labels: { padding: 15, usePointStyle: true } },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.85)',
                        padding: 14,
                        cornerRadius: 10,
                        mode: 'index'
                    }
                },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#e5e7eb' } },
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

