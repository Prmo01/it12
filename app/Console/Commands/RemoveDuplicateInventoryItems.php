<?php

namespace App\Console\Commands;

use App\Models\InventoryItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RemoveDuplicateInventoryItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:remove-duplicates {--dry-run : Show duplicates without deleting them}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove duplicate inventory items based on name';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        $this->info('Searching for duplicate inventory items...');

        // Find duplicate names
        $duplicates = DB::table('inventory_items')
            ->select('name', DB::raw('COUNT(*) as count'))
            ->whereNull('deleted_at')
            ->groupBy('name')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicates->isEmpty()) {
            $this->info('No duplicate inventory items found.');
            return 0;
        }

        $this->warn("Found {$duplicates->count()} duplicate item name(s):");
        $this->newLine();

        $totalToDelete = 0;

        foreach ($duplicates as $duplicate) {
            $items = InventoryItem::where('name', $duplicate->name)
                ->whereNull('deleted_at')
                ->orderBy('created_at', 'asc')
                ->get();

            $this->line("  • {$duplicate->name} ({$duplicate->count} duplicates)");

            // Keep the first (oldest) item
            $keepItem = $items->first();
            $itemsToDelete = $items->skip(1);

            $this->line("    Keeping: {$keepItem->item_code} (created: {$keepItem->created_at->format('Y-m-d H:i:s')})");

            foreach ($itemsToDelete as $item) {
                $totalToDelete++;
                if ($isDryRun) {
                    $this->line("    Would delete: {$item->item_code} (created: {$item->created_at->format('Y-m-d H:i:s')})");
                } else {
                    // Check if item has any related records
                    $hasRelations = $item->stockMovements()->count() > 0
                        || $item->purchaseRequestItems()->count() > 0
                        || $item->quotationItems()->count() > 0
                        || $item->purchaseOrderItems()->count() > 0
                        || $item->goodsReceiptItems()->count() > 0
                        || $item->materialIssuanceItems()->count() > 0
                        || $item->supplierPrices()->count() > 0;

                    if ($hasRelations) {
                        $this->warn("    Skipping {$item->item_code} - has related records (soft delete instead)");
                        $item->delete(); // Soft delete
                    } else {
                        $this->line("    Deleting: {$item->item_code}");
                        $item->forceDelete(); // Hard delete
                    }
                }
            }
            $this->newLine();
        }

        if ($isDryRun) {
            $this->info("DRY RUN: Would delete {$totalToDelete} duplicate item(s).");
            $this->info("Run without --dry-run to actually delete them.");
        } else {
            $this->info("Successfully removed {$totalToDelete} duplicate item(s).");
        }

        return 0;
    }
}
