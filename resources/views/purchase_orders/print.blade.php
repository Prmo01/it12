<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Purchase Order: {{ $purchaseOrder->po_number }}</title>
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
            font-size: 12px;
            padding: 20px;
            color: #333;
        }
        
        .header { 
            text-align: center; 
            margin-bottom: 30px;
            border-bottom: 3px solid #333;
            padding-bottom: 20px;
        }
        
        .header h1 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #000;
        }
        
        .header p {
            margin: 5px 0;
            font-size: 14px;
        }
        
        .info-section {
            margin-bottom: 25px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 10px;
        }
        
        .info-label {
            font-weight: bold;
            width: 150px;
        }
        
        .info-value {
            flex: 1;
        }
        
        .two-column {
            display: flex;
            gap: 40px;
            margin-bottom: 25px;
        }
        
        .column {
            flex: 1;
        }
        
        table { 
            width: 100%; 
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th, td { 
            border: 1px solid #333; 
            padding: 10px 8px; 
            text-align: left;
        }
        
        th { 
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        
        td {
            text-align: left;
        }
        
        td:nth-child(2),
        td:nth-child(3),
        td:nth-child(4) {
            text-align: right;
        }
        
        tfoot th {
            background-color: #e0e0e0;
            font-size: 13px;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #333;
            font-size: 11px;
        }
        
        .footer-section {
            margin-bottom: 15px;
        }
        
        .footer-label {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .terms-conditions {
            margin-top: 30px;
            padding: 15px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }
        
        .terms-conditions h3 {
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .print-info {
            text-align: right;
            font-size: 10px;
            color: #666;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px dashed #ccc;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>PURCHASE ORDER</h1>
        <p><strong>PO Number:</strong> {{ $purchaseOrder->po_number }}</p>
        <p><strong>Date:</strong> {{ $purchaseOrder->po_date->format('F d, Y') }}</p>
        @if($purchaseOrder->purchaseRequest && $purchaseOrder->purchaseRequest->project)
        <p><strong>Project:</strong> {{ $purchaseOrder->purchaseRequest->project->name }} ({{ $purchaseOrder->purchaseRequest->project->project_code }})</p>
        @endif
    </div>
    
    <div class="two-column">
        <div class="column">
            <div class="info-section">
                <h3 style="margin-bottom: 10px; font-size: 14px; border-bottom: 1px solid #333; padding-bottom: 5px;">SUPPLIER INFORMATION</h3>
                <div class="info-row">
                    <span class="info-label">Name:</span>
                    <span class="info-value">{{ $purchaseOrder->supplier->name }}</span>
                </div>
                @if($purchaseOrder->supplier->contact_person)
                <div class="info-row">
                    <span class="info-label">Contact Person:</span>
                    <span class="info-value">{{ $purchaseOrder->supplier->contact_person }}</span>
                </div>
                @endif
                @if($purchaseOrder->supplier->address)
                <div class="info-row">
                    <span class="info-label">Address:</span>
                    <span class="info-value">{{ $purchaseOrder->supplier->address }}</span>
                </div>
                @endif
                @if($purchaseOrder->supplier->phone)
                <div class="info-row">
                    <span class="info-label">Phone:</span>
                    <span class="info-value">{{ $purchaseOrder->supplier->phone }}</span>
                </div>
                @endif
                @if($purchaseOrder->supplier->email)
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{{ $purchaseOrder->supplier->email }}</span>
                </div>
                @endif
            </div>
        </div>
        
        <div class="column">
            <div class="info-section">
                <h3 style="margin-bottom: 10px; font-size: 14px; border-bottom: 1px solid #333; padding-bottom: 5px;">ORDER INFORMATION</h3>
                @if($purchaseOrder->purchaseRequest)
                <div class="info-row">
                    <span class="info-label">Purchase Request:</span>
                    <span class="info-value">{{ $purchaseOrder->purchaseRequest->pr_number }}</span>
                </div>
                @endif
                @if($purchaseOrder->expected_delivery_date)
                <div class="info-row">
                    <span class="info-label">Expected Delivery:</span>
                    <span class="info-value">{{ $purchaseOrder->expected_delivery_date->format('F d, Y') }}</span>
                </div>
                @endif
                @if($purchaseOrder->delivery_address)
                <div class="info-row">
                    <span class="info-label">Delivery Address:</span>
                    <span class="info-value">{{ $purchaseOrder->delivery_address }}</span>
                </div>
                @endif
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value"><strong>{{ strtoupper($purchaseOrder->status) }}</strong></span>
                </div>
                @if($purchaseOrder->createdBy)
                <div class="info-row">
                    <span class="info-label">Created By:</span>
                    <span class="info-value">{{ $purchaseOrder->createdBy->name }}</span>
                </div>
                @endif
                @if($purchaseOrder->approvedBy && $purchaseOrder->approved_at)
                <div class="info-row">
                    <span class="info-label">Approved By:</span>
                    <span class="info-value">{{ $purchaseOrder->approvedBy->name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Approved Date:</span>
                    <span class="info-value">{{ $purchaseOrder->approved_at->format('F d, Y H:i') }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 40%;">Item Description</th>
                <th style="width: 10%;">Quantity</th>
                <th style="width: 15%;">Unit Price</th>
                <th style="width: 15%;">Total Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchaseOrder->items as $index => $item)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->inventoryItem->name }}</strong>
                        @if($item->inventoryItem->item_code)
                        <br><small style="color: #666;">Code: {{ $item->inventoryItem->item_code }}</small>
                        @endif
                        @if($item->specifications)
                        <br><small style="color: #666;">{{ $item->specifications }}</small>
                        @endif
                    </td>
                    <td style="text-align: right;">{{ number_format($item->quantity, 2) }}</td>
                    <td style="text-align: right;">&#8369;{{ number_format($item->unit_price, 2) }}</td>
                    <td style="text-align: right;"><strong>&#8369;{{ number_format($item->total_price, 2) }}</strong></td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" style="text-align: right;">Subtotal:</th>
                <th style="text-align: right;">&#8369;{{ number_format($purchaseOrder->subtotal, 2) }}</th>
            </tr>
            @if($purchaseOrder->tax_amount > 0)
            <tr>
                <th colspan="4" style="text-align: right;">Tax (12%):</th>
                <th style="text-align: right;">&#8369;{{ number_format($purchaseOrder->tax_amount, 2) }}</th>
            </tr>
            @endif
            <tr>
                <th colspan="4" style="text-align: right;">TOTAL AMOUNT:</th>
                <th style="text-align: right; font-size: 14px;">&#8369;{{ number_format($purchaseOrder->total_amount, 2) }}</th>
            </tr>
        </tfoot>
    </table>
    
    @if($purchaseOrder->terms_conditions)
    <div class="terms-conditions">
        <h3>Terms and Conditions</h3>
        <p>{{ $purchaseOrder->terms_conditions }}</p>
    </div>
    @endif
    
    @if($purchaseOrder->notes)
    <div class="info-section" style="margin-top: 20px;">
        <h3 style="margin-bottom: 10px; font-size: 14px; border-bottom: 1px solid #333; padding-bottom: 5px;">Notes</h3>
        <p>{{ $purchaseOrder->notes }}</p>
    </div>
    @endif
    
    <div class="print-info">
        <p><strong>Printed by:</strong> {{ $printedBy->name }} ({{ $printedBy->role->name ?? 'User' }})</p>
        <p><strong>Printed on:</strong> {{ now()->format('F d, Y h:i A') }}</p>
    </div>
</body>
</html>

