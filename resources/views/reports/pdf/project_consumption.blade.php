<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Project Consumption Report</title>
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
        
        .project-info {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #e0f2fe;
            border-left: 4px solid #2563eb;
            font-size: 11px;
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
        <h1>PROJECT CONSUMPTION REPORT</h1>
        <p><strong>Generated Date:</strong> {{ \Carbon\Carbon::now()->setTimezone('Asia/Manila')->format('F d, Y h:i A') }}</p>
    </div>
    
    @if(isset($filters) && isset($filters['project_id']))
        @php
            $project = \App\Models\Project::find($filters['project_id']);
        @endphp
        @if($project)
        <div class="project-info">
            <strong>Project:</strong> {{ $project->name }}<br>
            <strong>Project Code:</strong> {{ $project->project_code ?? 'N/A' }}<br>
            @if($project->start_date)
            <strong>Start Date:</strong> {{ $project->start_date->format('F d, Y') }}<br>
            @endif
            @if($project->end_date)
            <strong>End Date:</strong> {{ $project->end_date->format('F d, Y') }}
            @endif
        </div>
        @endif
    @endif
    
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
                <th style="width: 12%;">Issuance Date</th>
                <th style="width: 15%;">MI Number</th>
                <th style="width: 15%;">Item Code</th>
                <th style="width: 25%;">Item Name</th>
                <th style="width: 13%;">Quantity</th>
                <th style="width: 15%;">Unit</th>
            </tr>
        </thead>
        <tbody>
            @php
                $rowNumber = 1;
            @endphp
            @forelse($data as $issuance)
                @foreach($issuance->items as $item)
                    <tr>
                        <td class="text-center">{{ $rowNumber++ }}</td>
                        <td>
                            {{ $issuance->issuance_date ? $issuance->issuance_date->format('M d, Y') : 'N/A' }}
                        </td>
                        <td>
                            <strong>{{ $issuance->issuance_number ?? 'N/A' }}</strong>
                        </td>
                        <td>
                            {{ $item->inventoryItem->item_code ?? 'N/A' }}
                        </td>
                        <td>
                            <div><strong>{{ $item->inventoryItem->name ?? 'N/A' }}</strong></div>
                        </td>
                        <td class="text-right">
                            <strong>{{ number_format($item->quantity, 2) }}</strong>
                        </td>
                        <td>
                            {{ $item->inventoryItem->unit_of_measure ?? 'N/A' }}
                        </td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px;">
                        No consumption data found for the selected criteria.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    @if($data->isNotEmpty())
    <div class="summary">
        <strong>Summary:</strong><br>
        Total Issuances: {{ $data->count() }}<br>
        Total Items Issued: {{ $data->sum(function($issuance) { return $issuance->items->count(); }) }}<br>
        Total Quantity: {{ number_format($data->sum(function($issuance) { return $issuance->items->sum('quantity'); }), 2) }}
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

