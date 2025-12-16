<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Purchase History Report</title>
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
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .status-draft {
            color: #6b7280;
        }
        
        .status-pending {
            color: #f59e0b;
        }
        
        .status-approved {
            color: #2563eb;
        }
        
        .status-completed {
            color: #10b981;
        }
        
        .status-cancelled {
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
        <h1>PURCHASE HISTORY REPORT</h1>
        <p><strong>Generated Date:</strong> {{ now()->format('F d, Y h:i A') }}</p>
    </div>
    
    @if(isset($filters) && (isset($filters['supplier_id']) || isset($filters['status']) || isset($filters['date_from']) || isset($filters['date_to'])))
    <div class="filter-info">
        <strong>Filters Applied:</strong><br>
        @if(isset($filters['supplier_id']) && $filters['supplier_id'])
            @php $supplier = \App\Models\Supplier::find($filters['supplier_id']); @endphp
            <strong>Supplier:</strong> {{ $supplier ? $supplier->name : 'N/A' }}<br>
        @endif
        @if(isset($filters['status']) && $filters['status'])
            <strong>Status:</strong> {{ ucfirst($filters['status']) }}<br>
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
                <th style="width: 15%;">PO Number</th>
                <th style="width: 12%;">Date</th>
                <th style="width: 25%;">Supplier</th>
                <th style="width: 13%;">Status</th>
                <th style="width: 15%;">Items</th>
                <th style="width: 15%;">Total Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $po)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $po->po_number ?? 'N/A' }}</strong>
                    </td>
                    <td>
                        {{ $po->po_date ? $po->po_date->format('M d, Y') : 'N/A' }}
                    </td>
                    <td>
                        <div><strong>{{ $po->supplier->name ?? 'N/A' }}</strong></div>
                        @if($po->supplier && $po->supplier->email)
                        <small style="color: #666;">{{ $po->supplier->email }}</small>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="status-{{ $po->status }}">
                            {{ ucfirst($po->status) }}
                        </span>
                    </td>
                    <td class="text-center">
                        {{ $po->items->count() }} items
                    </td>
                    <td class="text-right">
                        <strong>₱{{ number_format($po->total_amount ?? 0, 2) }}</strong>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px;">
                        No purchase orders found for the selected criteria.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    @if($data->isNotEmpty())
    <div class="summary">
        <strong>Summary:</strong><br>
        Total Orders: {{ $data->count() }}<br>
        Completed Orders: {{ $data->where('status', 'completed')->count() }}<br>
        Total Amount: ₱{{ number_format($data->sum('total_amount'), 2) }}
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

