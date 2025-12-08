@push('scripts')
<script>
    // Helper function to create gradient
    function createGradient(ctx, color1, color2) {
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, color1);
        gradient.addColorStop(1, color2);
        return gradient;
    }

    // Goods Receipts Status Chart
    const receiptStatusChartEl = document.getElementById('receiptStatusChart');
    if (receiptStatusChartEl) {
        const receiptCtx = receiptStatusChartEl.getContext('2d');
        const receiptLabels = {!! json_encode(array_keys($receiptStatusData->toArray())) !!};
        const receiptData = {!! json_encode(array_values($receiptStatusData->toArray())) !!};
        
        new Chart(receiptCtx, {
            type: 'doughnut',
            data: {
                labels: receiptLabels,
                datasets: [{
                    data: receiptData,
                    backgroundColor: [
                        '#2563eb',
                        '#10b981',
                        '#f59e0b',
                        '#ef4444',
                        '#6b7280'
                    ],
                    borderWidth: 3,
                    borderColor: '#ffffff',
                    hoverOffset: 12
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    animateRotate: true,
                    duration: 1500
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    },
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

    // Monthly Approvals Trend Chart
    const monthlyApprovalsChartEl = document.getElementById('monthlyApprovalsChart');
    if (monthlyApprovalsChartEl) {
        const approvalsCtx = monthlyApprovalsChartEl.getContext('2d');
        const approvalsGradient = createGradient(approvalsCtx, 'rgba(16, 185, 129, 0.3)', 'rgba(16, 185, 129, 0.05)');
        const approvalsLabels = {!! json_encode(array_keys($monthlyApprovals->toArray())) !!};
        const approvalsData = {!! json_encode(array_values($monthlyApprovals->toArray())) !!};
        
        new Chart(approvalsCtx, {
            type: 'line',
            data: {
                labels: approvalsLabels.map(month => {
                    const date = new Date(month + '-01');
                    return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                }),
                datasets: [{
                    label: 'Approved Receipts',
                    data: approvalsData,
                    borderColor: '#10b981',
                    backgroundColor: approvalsGradient,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: '#10b981',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 2000
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.85)',
                        padding: 14,
                        cornerRadius: 10
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#e5e7eb',
                            drawBorder: false
                        },
                        ticks: {
                            stepSize: 1
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
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
                animation: {
                    duration: 1500
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            padding: 15,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.85)',
                        padding: 14,
                        cornerRadius: 10,
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#e5e7eb',
                            drawBorder: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }
</script>
@endpush

