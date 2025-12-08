@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function createGradient(ctx, color1, color2) {
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, color1);
        gradient.addColorStop(1, color2);
        return gradient;
    }

    // Receipt Status Chart
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

    // Monthly Receipts Chart
    const monthlyReceiptsChartEl = document.getElementById('monthlyReceiptsChart');
    if (monthlyReceiptsChartEl) {
        const receiptsCtx = monthlyReceiptsChartEl.getContext('2d');
        const receiptsGradient = createGradient(receiptsCtx, 'rgba(16, 185, 129, 0.3)', 'rgba(16, 185, 129, 0.05)');
        const receiptsLabels = {!! json_encode(array_keys($monthlyReceipts->toArray())) !!};
        const receiptsData = {!! json_encode(array_values($monthlyReceipts->toArray())) !!};
        
        new Chart(receiptsCtx, {
            type: 'line',
            data: {
                labels: receiptsLabels.map(month => {
                    const date = new Date(month + '-01');
                    return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                }),
                datasets: [{
                    label: 'Goods Receipts',
                    data: receiptsData,
                    borderColor: '#10b981',
                    backgroundColor: receiptsGradient,
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

    // Monthly Issuances Chart
    const monthlyIssuancesChartEl = document.getElementById('monthlyIssuancesChart');
    if (monthlyIssuancesChartEl) {
        const issuancesCtx = monthlyIssuancesChartEl.getContext('2d');
        const issuancesGradient = createGradient(issuancesCtx, 'rgba(239, 68, 68, 0.3)', 'rgba(239, 68, 68, 0.05)');
        const issuancesLabels = {!! json_encode(array_keys($monthlyIssuances->toArray())) !!};
        const issuancesData = {!! json_encode(array_values($monthlyIssuances->toArray())) !!};
        
        new Chart(issuancesCtx, {
            type: 'line',
            data: {
                labels: issuancesLabels.map(month => {
                    const date = new Date(month + '-01');
                    return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                }),
                datasets: [{
                    label: 'Material Issuances',
                    data: issuancesData,
                    borderColor: '#ef4444',
                    backgroundColor: issuancesGradient,
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

