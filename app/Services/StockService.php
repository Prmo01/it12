<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\StockMovement;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\MaterialIssuance;
use App\Models\MaterialIssuanceItem;
use Illuminate\Support\Facades\DB;

class StockService
{
    public function recordStockMovement(
        int $inventoryItemId,
        string $movementType,
        float $quantity,
        float $unitCost = 0,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $notes = null
    ): StockMovement {
        return DB::transaction(function () use ($inventoryItemId, $movementType, $quantity, $unitCost, $referenceType, $referenceId, $notes) {
            $item = InventoryItem::findOrFail($inventoryItemId);
            $currentStock = $this->getCurrentStock($inventoryItemId);

            $balanceAfter = match($movementType) {
                'stock_in', 'adjustment_in', 'return_in' => $currentStock + $quantity,
                'stock_out', 'adjustment_out', 'return_out' => $currentStock - $quantity,
                default => $currentStock,
            };

            $movement = StockMovement::create([
                'inventory_item_id' => $inventoryItemId,
                'movement_type' => $movementType,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'balance_after' => $balanceAfter,
                'notes' => $notes,
                'created_by' => auth()->id(),
            ]);

            return $movement;
        });
    }

    public function getCurrentStock(?int $inventoryItemId): float
    {
        if (!$inventoryItemId) {
            return 0.0;
        }

        $latestMovement = StockMovement::where('inventory_item_id', $inventoryItemId)
            ->orderBy('created_at', 'desc')
            ->first();

        return $latestMovement ? (float) $latestMovement->balance_after : 0.0;
    }

    public function processGoodsReceipt(GoodsReceipt $gr): void
    {
        DB::transaction(function () use ($gr) {
            // Refresh to ensure we have the latest status
            $gr->refresh();
            
            if ($gr->status !== 'approved') {
                return;
            }

            // Ensure items and purchaseOrderItem relationships are loaded
            if (!$gr->relationLoaded('items')) {
                $gr->load('items.purchaseOrderItem');
            }

            foreach ($gr->items as $item) {
                if ($item->quantity_accepted > 0) {
                    // Ensure purchaseOrderItem is loaded
                    if (!$item->relationLoaded('purchaseOrderItem')) {
                        $item->load('purchaseOrderItem');
                    }
                    
                    $this->recordStockMovement(
                        $item->inventory_item_id,
                        'stock_in',
                        $item->quantity_accepted,
                        $item->purchaseOrderItem->unit_price ?? 0,
                        GoodsReceipt::class,
                        $gr->id,
                        "Goods Receipt: {$gr->gr_number}"
                    );
                }
            }
        });
    }

    public function processMaterialIssuance(MaterialIssuance $issuance): void
    {
        DB::transaction(function () use ($issuance) {
            // Refresh to ensure we have the latest status
            $issuance->refresh();
            
            if ($issuance->status !== 'issued') {
                return;
            }

            // Ensure items relationship is loaded
            if (!$issuance->relationLoaded('items')) {
                $issuance->load('items');
            }

            foreach ($issuance->items as $item) {
                $this->recordStockMovement(
                    $item->inventory_item_id,
                    'stock_out',
                    $item->quantity,
                    $item->unit_cost ?? 0,
                    MaterialIssuance::class,
                    $issuance->id,
                    "Material Issuance: {$issuance->issuance_number}"
                );
            }
        });
    }

    public function processGoodsReturn(int $inventoryItemId, float $quantity, int $returnId): void
    {
        DB::transaction(function () use ($inventoryItemId, $quantity, $returnId) {
            $this->recordStockMovement(
                $inventoryItemId,
                'return_out',
                $quantity,
                0,
                'App\Models\GoodsReturn',
                $returnId,
                "Goods Return"
            );
        });
    }

    public function adjustStock(int $inventoryItemId, float $quantity, string $type, ?string $notes = null): StockMovement
    {
        if (!in_array($type, ['adjustment_in', 'adjustment_out'])) {
            throw new \InvalidArgumentException("Invalid adjustment type: {$type}");
        }

        return $this->recordStockMovement(
            $inventoryItemId,
            $type,
            abs($quantity),
            0,
            null,
            null,
            $notes ?? "Manual stock adjustment"
        );
    }

    public function checkReorderLevel(int $inventoryItemId): bool
    {
        $item = InventoryItem::findOrFail($inventoryItemId);
        $currentStock = $this->getCurrentStock($inventoryItemId);

        return $currentStock <= $item->reorder_level;
    }
}

