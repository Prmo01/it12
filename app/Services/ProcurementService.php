<?php

namespace App\Services;

use App\Models\PurchaseRequest;
use App\Models\Quotation;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProcurementService
{
    public function createPurchaseRequest(array $data): PurchaseRequest
    {
        return DB::transaction(function () use ($data) {
            $items = $data['items'] ?? [];
            unset($data['items']);

            $pr = PurchaseRequest::create($data);

            foreach ($items as $item) {
                $pr->items()->create($item);
            }

            return $pr->fresh();
        });
    }

    public function approvePurchaseRequest(PurchaseRequest $pr, int $approvedBy): PurchaseRequest
    {
        return DB::transaction(function () use ($pr, $approvedBy) {
            $pr->update([
                'status' => 'approved',
                'approved_by' => $approvedBy,
                'approved_at' => now(),
            ]);

            return $pr->fresh();
        });
    }

    public function createQuotation(array $data): Quotation
    {
        return DB::transaction(function () use ($data) {
            $items = $data['items'] ?? [];
            unset($data['items']);

            // Generate unique quotation number
            do {
                $quotationNumber = 'QT-' . strtoupper(Str::random(8));
            } while (Quotation::where('quotation_number', $quotationNumber)->exists());

            $data['quotation_number'] = $quotationNumber;
            $data['status'] = 'pending';

            // Get project_code from purchase_request
            if (isset($data['purchase_request_id'])) {
                $purchaseRequest = PurchaseRequest::with('project')->find($data['purchase_request_id']);
                if ($purchaseRequest && $purchaseRequest->project) {
                    $data['project_code'] = $purchaseRequest->project->project_code;
                }
            }

            $quotation = Quotation::create($data);

            $totalAmount = 0;
            foreach ($items as $item) {
                $item['total_price'] = $item['quantity'] * $item['unit_price'];
                $totalAmount += $item['total_price'];
                $quotation->items()->create($item);
            }

            $quotation->update(['total_amount' => $totalAmount]);

            return $quotation->fresh();
        });
    }

    public function createPurchaseOrderFromQuotation(Quotation $quotation, array $additionalData = []): PurchaseOrder
    {
        return DB::transaction(function () use ($quotation, $additionalData) {
            // Generate unique PO number
            do {
                $poNumber = 'PO-' . strtoupper(Str::random(8));
            } while (PurchaseOrder::where('po_number', $poNumber)->exists());

            // Get project_code from quotation
            $projectCode = $quotation->project_code;
            if (!$projectCode && $quotation->purchaseRequest && $quotation->purchaseRequest->project) {
                $projectCode = $quotation->purchaseRequest->project->project_code;
            }

            $po = PurchaseOrder::create([
                'po_number' => $poNumber,
                'project_code' => $projectCode,
                'purchase_request_id' => $quotation->purchase_request_id,
                'quotation_id' => $quotation->id,
                'supplier_id' => null, // No single supplier - suppliers are per item
                'po_date' => now(),
                'expected_delivery_date' => $additionalData['expected_delivery_date'] ?? null,
                'status' => 'draft',
                'terms_conditions' => $additionalData['terms_conditions'] ?? $quotation->terms_conditions,
                'delivery_address' => $additionalData['delivery_address'] ?? null,
                'created_by' => auth()->id(),
                ...$additionalData,
            ]);

            $subtotal = 0;
            foreach ($quotation->items as $quotationItem) {
                $poItem = PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'supplier_id' => $quotationItem->supplier_id, // Copy supplier from quotation item
                    'inventory_item_id' => $quotationItem->inventory_item_id,
                    'quantity' => $quotationItem->quantity,
                    'unit_price' => $quotationItem->unit_price,
                    'total_price' => $quotationItem->total_price,
                    'specifications' => $quotationItem->specifications,
                ]);
                $subtotal += $poItem->total_price;
            }

            $taxAmount = $subtotal * 0.1; // 10% tax
            $po->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $subtotal + $taxAmount,
            ]);

            // Update PR status
            if ($quotation->purchaseRequest) {
                $quotation->purchaseRequest->update(['status' => 'converted_to_po']);
            }

            // Update quotation status
            $quotation->update(['status' => 'accepted']);

            return $po->fresh();
        });
    }

    public function approvePurchaseOrder(PurchaseOrder $po, int $approvedBy): PurchaseOrder
    {
        return DB::transaction(function () use ($po, $approvedBy) {
            $po->update([
                'status' => 'approved',
                'approved_by' => $approvedBy,
                'approved_at' => now(),
            ]);

            return $po->fresh();
        });
    }
}

