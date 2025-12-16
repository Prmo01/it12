<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Supplier Performance Report</title>
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
        
        .rate-excellent {
            color: #10b981;
            font-weight: bold;
        }
        
        .rate-good {
            color: #f59e0b;
            font-weight: bold;
        }
        
        .rate-poor {
            color: #ef4444;
            font-weight: bold;
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
        <p style="font-size: 14px; font-weight: bold; margin-bottom: 5px;">Davao Modern Glass and Alluminum Supply Corp.</p>
        <h1>SUPPLIER PERFORMANCE REPORT</h1>
        <p><strong>Generated Date:</strong> {{ \Carbon\Carbon::now()->setTimezone('Asia/Manila')->format('F d, Y h:i A') }}</p>
    </div>
    
    @if(isset($filters) && (isset($filters['date_from']) || isset($filters['date_to'])))
    <div class="filter-info">
        <strong>Filters Applied:</strong><br>
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
                <th style="width: 30%;">Supplier</th>
                <th style="width: 15%;">Total Orders</th>
                <th style="width: 15%;">Completed Orders</th>
                <th style="width: 15%;">On-Time Deliveries</th>
                <th style="width: 20%;">On-Time Rate</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <div><strong>{{ $item['supplier']->name ?? 'N/A' }}</strong></div>
                        @if(isset($item['supplier']) && $item['supplier']->email)
                        <small style="color: #666;">{{ $item['supplier']->email }}</small>
                        @endif
                    </td>
                    <td class="text-center">
                        <strong>{{ $item['total_orders'] ?? 0 }}</strong>
                    </td>
                    <td class="text-center">
                        <strong>{{ $item['completed_orders'] ?? 0 }}</strong>
                    </td>
                    <td class="text-center">
                        <strong>{{ $item['on_time_deliveries'] ?? 0 }}</strong>
                    </td>
                    <td class="text-center">
                        @php
                            $rate = $item['on_time_rate'] ?? 0;
                            $rateClass = $rate >= 80 ? 'rate-excellent' : ($rate >= 60 ? 'rate-good' : 'rate-poor');
                        @endphp
                        <span class="{{ $rateClass }}">
                            {{ number_format($rate, 1) }}%
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 20px;">
                        No supplier performance data found for the selected criteria.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    @if($data->isNotEmpty())
    <div class="summary">
        <strong>Summary:</strong><br>
        Total Suppliers: {{ $data->count() }}<br>
        Total Orders: {{ $data->sum('total_orders') }}<br>
        Average On-Time Rate: {{ number_format($data->avg('on_time_rate'), 1) }}%
    </div>
    @endif
    
    <div class="print-info">
        <p><strong>Printed by:</strong> {{ $printedBy->name ?? 'System' }} ({{ $printedBy->role->name ?? 'User' }})</p>
        <p><strong>Printed on:</strong> {{ \Carbon\Carbon::now()->setTimezone('Asia/Manila')->format('F d, Y h:i A') }}</p>
    </div>
    
    <div class="footer">
        <p>This is a computer-generated report. No signature required.</p>
    </div>
</body>
</html>

