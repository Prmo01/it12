<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Inventory Movement Report</title>
    <style>
        @page {
            margin: 20mm;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body { 
            font-family: 'Arial', sans-serif; 
            font-size: 11px;
            color: #333;
        }
        
        .header { 
            text-align: center; 
            margin-bottom: 25px;
            border-bottom: 3px solid #333;
            padding-bottom: 15px;
        }
        
        .header h1 {
            font-size: 22px;
            margin-bottom: 8px;
            color: #000;
        }
        
        .header p {
            margin: 4px 0;
            font-size: 12px;
        }
        
        .filter-info {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f5f5f5;
            border-left: 4px solid #2563eb;
            font-size: 10px;
        }
        
        .filter-info strong {
            display: inline-block;
            width: 120px;
        }
        
        table { 
            width: 100%; 
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 10px;
        }
        
        th, td { 
            border: 1px solid #333; 
            padding: 8px 6px; 
            text-align: left;
        }
        
        th { 
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
            font-size: 10px;
        }
        
        td {
            text-align: left;
        }
        
        td:nth-child(3),
        td:nth-child(4),
        td:nth-child(5) {
            text-align: right;
        }
        
        .text-success {
            color: #10b981;
        }
        
        .text-danger {
            color: #ef4444;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #333;
            font-size: 9px;
            text-align: center;
        }
        
        .print-info {
            text-align: right;
            font-size: 9px;
            color: #666;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px dashed #ccc;
        }
        
        .summary {
            margin-top: 15px;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>INVENTORY MOVEMENT REPORT</h1>
        <p><strong>Generated Date:</strong> {{ now()->format('F d, Y h:i A') }}</p>
    </div>
    
    @if(isset($filters) && (isset($filters['item_id']) || isset($filters['movement_type']) || isset($filters['date_from']) || isset($filters['date_to'])))
    <div class="filter-info">
        <strong>Filters Applied:</strong><br>
        @if(isset($filters['item_id']) && $filters['item_id'])
            @php $item = \App\Models\InventoryItem::find($filters['item_id']); @endphp
            <strong>Item:</strong> {{ $item ? $item->name : 'N/A' }}<br>
        @endif
        @if(isset($filters['movement_type']) && $filters['movement_type'])
            <strong>Movement Type:</strong> {{ ucfirst(str_replace('_', ' ', $filters['movement_type'])) }}<br>
        @endif
        @if(isset($filters['date_from']) && $filters['date_from'])
            <strong>Date From:</strong> {{ \Carbon\Carbon::parse($filters['date_from'])->format('F d, Y') }}<br>
        @endif
        @if(isset($filters['date_to']) && $filters['date_to'])
            <strong>Date To:</strong> {{ \Carbon\Carbon::parse($filters['date_to'])->format('F d, Y') }}
        @endif
    </div>
    @endif
    
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 18%;">Date & Time</th>
                <th style="width: 30%;">Item</th>
                <th style="width: 15%;">Movement Type</th>
                <th style="width: 12%;">Quantity</th>
                <th style="width: 12%;">Balance After</th>
                <th style="width: 8%;">Reference</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $movement)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>
                        <div><strong>{{ $movement->created_at->format('M d, Y') }}</strong></div>
                        <small>{{ $movement->created_at->format('h:i A') }}</small>
                    </td>
                    <td>
                        <div><strong>{{ $movement->inventoryItem->name }}</strong></div>
                        @if($movement->inventoryItem->item_code)
                        <small style="color: #666;">Code: {{ $movement->inventoryItem->item_code }}</small>
                        @endif
                    </td>
                    <td>
                        <span class="{{ str_contains($movement->movement_type, 'in') ? 'text-success' : 'text-danger' }}">
                            {{ ucfirst(str_replace('_', ' ', $movement->movement_type)) }}
                        </span>
                    </td>
                    <td style="text-align: right;" class="{{ str_contains($movement->movement_type, 'in') ? 'text-success' : 'text-danger' }}">
                        {{ str_contains($movement->movement_type, 'in') ? '+' : '-' }}{{ number_format($movement->quantity, 2) }}
                        <br><small>{{ $movement->inventoryItem->unit_of_measure }}</small>
                    </td>
                    <td style="text-align: right;">
                        <strong>{{ number_format($movement->balance_after, 2) }}</strong>
                        <br><small>{{ $movement->inventoryItem->unit_of_measure }}</small>
                    </td>
                    <td style="text-align: center; font-size: 9px;">
                        @if($movement->reference_type)
                            @php
                                $refParts = explode('\\', $movement->reference_type);
                                $refName = end($refParts);
                            @endphp
                            {{ str_replace('App\\Models\\', '', $refName) }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px;">
                        No inventory movements found for the selected criteria.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    @if($data->isNotEmpty())
    <div class="summary">
        <strong>Summary:</strong><br>
        Total Movements: {{ $data->count() }}<br>
        @php
            $inMovements = $data->whereIn('movement_type', ['stock_in', 'adjustment_in', 'return_in'])->count();
            $outMovements = $data->whereIn('movement_type', ['stock_out', 'adjustment_out', 'return_out'])->count();
        @endphp
        In Movements: {{ $inMovements }} | Out Movements: {{ $outMovements }}
    </div>
    @endif
    
    <div class="print-info">
        <p><strong>Printed by:</strong> {{ $printedBy->name ?? 'System' }} ({{ $printedBy->role->name ?? 'User' }})</p>
        <p><strong>Printed on:</strong> {{ now()->format('F d, Y h:i A') }}</p>
    </div>
    
    <div class="footer">
        <p>This is a computer-generated report. No signature required.</p>
    </div>
</body>
</html>

