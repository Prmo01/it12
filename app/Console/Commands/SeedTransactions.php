<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\GoodsReturn;
use App\Models\GoodsReturnItem;
use App\Models\MaterialIssuance;
use App\Models\MaterialIssuanceItem;
use App\Models\ChangeOrder;
use App\Models\InventoryItem;
use App\Models\Supplier;
use App\Models\User;
use App\Models\StockMovement;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SeedTransactions extends Command
{
    protected $signature = 'transactions:seed {--total=80 : Total number of transactions to create}';
    protected $description = 'Create 80 linked transactions across all modules following the system flow';

    protected $prsCreated = 0;
    protected $quotationsCreated = 0;
    protected $posCreated = 0;
    protected $grsCreated = 0;
    protected $goodsReturnsCreated = 0;
    protected $issuancesCreated = 0;
    protected $changeOrdersCreated = 0;
    protected $completedProjectsCreated = 0;

    /**
     * Get the next unique number for a given prefix and table
     */
    protected function getNextUniqueNumber(string $prefix, string $table, string $numberColumn): string
    {
        $lastRecord = \DB::table($table)
            ->orderByRaw("CAST(SUBSTRING({$numberColumn}, " . (strlen($prefix) + 1) . ") AS UNSIGNED) DESC")
            ->first();
        
        $lastNumber = $lastRecord ? (int) substr($lastRecord->{$numberColumn}, strlen($prefix) + 1) : 0;
        $nextNumber = $lastNumber + 1;
        
        while (\DB::table($table)->where($numberColumn, $prefix . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT))->exists()) {
            $nextNumber++;
        }
        
        return $prefix . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    public function handle()
    {
        $targetTotal = (int) $this->option('total');
        $this->info("Creating {$targetTotal} linked transactions across all modules...");

        // Get users
        $adminUser = User::whereHas('role', fn($q) => $q->where('slug', 'admin'))->first();
        $pm = User::whereHas('role', fn($q) => $q->where('slug', 'project_manager'))->first();
        $purchasingUser = User::whereHas('role', fn($q) => $q->where('slug', 'purchasing'))->first();
        $inventoryManager = User::whereHas('role', fn($q) => $q->where('slug', 'inventory_manager'))->first();
        $warehouseManager = User::whereHas('role', fn($q) => $q->where('slug', 'warehouse_manager'))->first() ?? $inventoryManager;

        if (!$adminUser || !$pm || !$purchasingUser || !$inventoryManager) {
            $this->error("Required users not found. Please run: php artisan db:seed");
            return 1;
        }

        $suppliers = Supplier::all();
        $inventoryItems = InventoryItem::all();
        
        if ($suppliers->isEmpty() || $inventoryItems->isEmpty()) {
            $this->error("Suppliers or Inventory Items not found. Please run: php artisan db:seed");
            return 1;
        }

        // Get existing projects or create new ones if needed
        $existingProjects = Project::all();
        
        if ($existingProjects->isEmpty()) {
            $this->error("No projects found. Please run: php artisan db:seed first to create projects.");
            return 1;
        }

        // Generate dates across last 6 months
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $months[] = now()->subMonths($i)->format('Y-m');
        }

        $this->info("Distributing transactions across months: " . implode(', ', $months));
        $this->info("Found {$existingProjects->count()} existing projects.");

        // Track created transactions for linking
        $createdGRs = [];
        $createdPOs = [];
        $createdProjects = [];

        // Process each project to create linked transactions
        foreach ($existingProjects as $projectIndex => $project) {
            if ($this->getTotalTransactions() >= $targetTotal) {
                break;
            }

            $this->info("Processing project: {$project->name} ({$project->project_code})...");
            
            // Mark some projects as completed (about 20% of projects)
            if ($projectIndex % 5 === 0 && $project->status !== 'completed') {
                $project->update([
                    'status' => 'completed',
                    'progress_percentage' => 100,
                ]);
                $this->completedProjectsCreated++;
                $this->info("  ✓ Marked project as completed");
            }

            // Create Purchase Requests (3-5 per project)
            $prCount = min(5, max(3, (int) (($targetTotal - $this->getTotalTransactions()) / ($existingProjects->count() - $projectIndex + 1))));
            $prCount = min($prCount, 5);
            
            for ($prIdx = 0; $prIdx < $prCount && $this->getTotalTransactions() < $targetTotal; $prIdx++) {
                $monthOffset = $prIdx % count($months);
                $targetMonth = $months[$monthOffset];
                $daysInMonth = Carbon::parse($targetMonth . '-01')->daysInMonth;
                $randomDay = rand(1, $daysInMonth);
                $prDate = Carbon::parse($targetMonth . '-' . str_pad($randomDay, 2, '0', STR_PAD_LEFT));
                
                $prStatuses = ['approved', 'approved', 'approved', 'submitted', 'draft'];
                $prStatus = $prStatuses[$prIdx % count($prStatuses)];
                
                $prPurposes = [
                    'Window and door fabrication materials',
                    'Glass panel installation supplies',
                    'Aluminum framework structural components',
                    'Modular cabinet system hardware',
                    'Curtain wall glazing materials',
                    'Interior partition and divider supplies',
                    'Exterior facade cladding materials',
                    'UPVC window system components',
                    'Cabinet door and drawer mechanisms',
                    'Aluminum railing and balustrade supplies',
                ];
                
                $purpose = $prPurposes[$prIdx % count($prPurposes)] . " for {$project->name}";
                
                $pr = PurchaseRequest::create([
                    'pr_number' => $this->getNextUniqueNumber('PR', 'purchase_requests', 'pr_number'),
                    'project_id' => $project->id,
                    'purpose' => $purpose,
                    'status' => $prStatus,
                    'requested_by' => $pm->id,
                    'approved_by' => ($prStatus === 'approved') ? $adminUser->id : null,
                    'approved_at' => ($prStatus === 'approved') ? $prDate->copy()->addDays(rand(1, 5)) : null,
                    'created_at' => $prDate,
                    'updated_at' => $prDate,
                ]);

                // Add items to PR
                $itemCount = rand(3, 6);
                $selectedItems = $inventoryItems->random(min($itemCount, $inventoryItems->count()));
                foreach ($selectedItems as $item) {
                    PurchaseRequestItem::create([
                        'purchase_request_id' => $pr->id,
                        'inventory_item_id' => $item->id,
                        'quantity' => rand(20, 300),
                        'unit_cost' => rand(50, 5000) / 100,
                        'specifications' => "Standard specifications for {$item->name}",
                    ]);
                }
                
                $this->prsCreated++;
                $this->info("  ✓ Created PR: {$pr->pr_number}");

                // Create Quotations for approved PRs (2-3 per PR)
                if ($prStatus === 'approved' && $this->getTotalTransactions() < $targetTotal) {
                    $quoteCount = rand(2, 3);
                    $acceptedQuotation = null;
                    
                    for ($qIdx = 0; $qIdx < $quoteCount && $this->getTotalTransactions() < $targetTotal; $qIdx++) {
                        $quoteDate = $prDate->copy()->addDays(rand(1, 10));
                        $supplier = $suppliers->random();
                        
                        // Ensure at least one accepted quotation
                        $quoteStatuses = ['accepted', 'pending', 'rejected'];
                        $quoteStatus = ($qIdx === 0) ? 'accepted' : ($quoteStatuses[$qIdx % count($quoteStatuses)]);
                        
                        $quotation = Quotation::create([
                            'quotation_number' => $this->getNextUniqueNumber('QT', 'quotations', 'quotation_number'),
                            'project_code' => $project->project_code,
                            'purchase_request_id' => $pr->id,
                            'supplier_id' => $supplier->id,
                            'quotation_date' => $quoteDate,
                            'valid_until' => $quoteDate->copy()->addDays(rand(30, 60)),
                            'status' => $quoteStatus,
                            'terms_conditions' => 'Standard payment terms: Net 30 days',
                            'notes' => "Quotation from {$supplier->name} for {$project->name}",
                            'created_at' => $quoteDate,
                            'updated_at' => $quoteDate,
                        ]);

                        // Add items to quotation
                        $totalAmount = 0;
                        foreach ($pr->items as $prItem) {
                            $priceVariation = 0.85 + (rand(0, 30) / 100); // 85% to 115% of PR price
                            $unitPrice = $prItem->unit_cost * $priceVariation;
                            $totalPrice = $unitPrice * $prItem->quantity;
                            $totalAmount += $totalPrice;
                            
                            QuotationItem::create([
                                'quotation_id' => $quotation->id,
                                'inventory_item_id' => $prItem->inventory_item_id,
                                'supplier_id' => $supplier->id,
                                'quantity' => $prItem->quantity,
                                'unit_price' => round($unitPrice, 2),
                                'total_price' => round($totalPrice, 2),
                                'specifications' => $prItem->specifications,
                            ]);
                        }
                        
                        $quotation->update(['total_amount' => round($totalAmount, 2)]);
                        $this->quotationsCreated++;
                        $this->info("    ✓ Created Quotation: {$quotation->quotation_number}");
                        
                        if ($quoteStatus === 'accepted') {
                            $acceptedQuotation = $quotation;
                        }
                    }

                    // Create Purchase Order from accepted quotation
                    if ($acceptedQuotation && $this->getTotalTransactions() < $targetTotal) {
                        $poDate = $acceptedQuotation->quotation_date->copy()->addDays(rand(2, 7));
                        $poStatuses = ['draft', 'pending', 'approved', 'approved', 'completed'];
                        $poStatus = $poStatuses[rand(0, count($poStatuses) - 1)];
                        
                        $po = PurchaseOrder::create([
                            'po_number' => $this->getNextUniqueNumber('PO', 'purchase_orders', 'po_number'),
                            'project_code' => $project->project_code,
                            'purchase_request_id' => $pr->id,
                            'quotation_id' => $acceptedQuotation->id,
                            'supplier_id' => $acceptedQuotation->supplier_id,
                            'po_date' => $poDate,
                            'expected_delivery_date' => $poDate->copy()->addDays(rand(14, 45)),
                            'status' => $poStatus,
                            'delivery_address' => 'Main Warehouse, 123 Industrial St., Davao City',
                            'terms_conditions' => 'Standard delivery terms apply',
                            'created_by' => $purchasingUser->id,
                            'approved_by' => ($poStatus === 'approved' || $poStatus === 'completed') ? $adminUser->id : null,
                            'approved_at' => ($poStatus === 'approved' || $poStatus === 'completed') ? $poDate->copy()->addDays(rand(1, 3)) : null,
                            'created_at' => $poDate,
                            'updated_at' => $poDate,
                        ]);

                        // Add items to PO
                        $subtotal = 0;
                        foreach ($acceptedQuotation->items as $qItem) {
                            PurchaseOrderItem::create([
                                'purchase_order_id' => $po->id,
                                'inventory_item_id' => $qItem->inventory_item_id,
                                'supplier_id' => $qItem->supplier_id,
                                'quantity' => $qItem->quantity,
                                'unit_price' => $qItem->unit_price,
                                'total_price' => $qItem->total_price,
                                'specifications' => $qItem->specifications,
                            ]);
                            $subtotal += $qItem->total_price;
                        }
                        
                        $taxAmount = $subtotal * 0.12;
                        $po->update([
                            'subtotal' => round($subtotal, 2),
                            'tax_amount' => round($taxAmount, 2),
                            'total_amount' => round($subtotal + $taxAmount, 2),
                        ]);
                        
                        $createdPOs[] = $po;
                        $this->posCreated++;
                        $this->info("    ✓ Created PO: {$po->po_number}");

                        // Create Goods Receipt for approved/completed POs
                        if (($poStatus === 'approved' || $poStatus === 'completed') && $this->getTotalTransactions() < $targetTotal) {
                            $grDate = $po->expected_delivery_date->copy()->subDays(rand(-5, 10));
                            $grStatuses = ['draft', 'pending', 'approved', 'approved'];
                            $grStatus = $grStatuses[rand(0, count($grStatuses) - 1)];
                            
                            $gr = GoodsReceipt::create([
                                'gr_number' => $this->getNextUniqueNumber('GR', 'goods_receipts', 'gr_number'),
                                'project_code' => $project->project_code,
                                'purchase_order_id' => $po->id,
                                'gr_date' => $grDate,
                                'status' => $grStatus,
                                'delivery_note_number' => 'DN-' . strtoupper(Str::random(8)),
                                'remarks' => "Goods received for {$project->name}",
                                'received_by' => $warehouseManager->id,
                                'approved_by' => ($grStatus === 'approved') ? $warehouseManager->id : null,
                                'approved_at' => ($grStatus === 'approved') ? $grDate->copy()->addDays(rand(1, 2)) : null,
                                'created_at' => $grDate,
                                'updated_at' => $grDate,
                            ]);

                            // Add items to Goods Receipt
                            foreach ($po->items as $poItem) {
                                $qtyReceived = $poItem->quantity;
                                $qtyAccepted = (int) ($qtyReceived * (0.90 + (rand(0, 10) / 100))); // 90-100% acceptance
                                $qtyRejected = $qtyReceived - $qtyAccepted;
                                
                                GoodsReceiptItem::create([
                                    'goods_receipt_id' => $gr->id,
                                    'purchase_order_item_id' => $poItem->id,
                                    'inventory_item_id' => $poItem->inventory_item_id,
                                    'quantity_ordered' => $poItem->quantity,
                                    'quantity_received' => $qtyReceived,
                                    'quantity_accepted' => $qtyAccepted,
                                    'quantity_rejected' => $qtyRejected,
                                    'rejection_reason' => $qtyRejected > 0 ? 'Minor defects or damage' : null,
                                ]);

                                // Create stock movement if approved
                                if ($grStatus === 'approved') {
                                    $latestMovement = StockMovement::where('inventory_item_id', $poItem->inventory_item_id)
                                        ->orderBy('created_at', 'desc')
                                        ->first();
                                    $currentStock = $latestMovement ? (float) $latestMovement->balance_after : 0;
                                    $balanceAfter = $currentStock + $qtyAccepted;

                                    StockMovement::create([
                                        'inventory_item_id' => $poItem->inventory_item_id,
                                        'movement_type' => 'stock_in',
                                        'reference_type' => 'App\Models\GoodsReceipt',
                                        'reference_id' => $gr->id,
                                        'quantity' => $qtyAccepted,
                                        'unit_cost' => $poItem->unit_price,
                                        'balance_after' => $balanceAfter,
                                        'notes' => "Stock in from GR {$gr->gr_number} for {$project->name}",
                                        'created_by' => $warehouseManager->id,
                                        'created_at' => $gr->approved_at ?? $grDate,
                                        'updated_at' => $gr->approved_at ?? $grDate,
                                    ]);
                                }
                            }
                            
                            $createdGRs[] = $gr;
                            $this->grsCreated++;
                            $this->info("      ✓ Created GR: {$gr->gr_number}");

                            // Create Goods Return for some approved GRs (about 30% of GRs)
                            if ($grStatus === 'approved' && rand(1, 100) <= 30 && $this->getTotalTransactions() < $targetTotal) {
                                $returnDate = $gr->approved_at->copy()->addDays(rand(1, 7));
                                $returnStatuses = ['pending', 'approved', 'approved'];
                                $returnStatus = $returnStatuses[rand(0, count($returnStatuses) - 1)];
                                
                                $goodsReturn = GoodsReturn::create([
                                    'return_number' => $this->getNextUniqueNumber('RT', 'goods_returns', 'return_number'),
                                    'project_code' => $project->project_code,
                                    'goods_receipt_id' => $gr->id,
                                    'return_date' => $returnDate,
                                    'status' => $returnStatus,
                                    'reason' => 'Defective items or quality issues',
                                    'returned_by' => $warehouseManager->id,
                                    'approved_by' => ($returnStatus === 'approved') ? $adminUser->id : null,
                                    'approved_at' => ($returnStatus === 'approved') ? $returnDate->copy()->addDays(rand(1, 3)) : null,
                                    'notes' => "Returning defective items from GR {$gr->gr_number} for {$project->name}",
                                    'created_at' => $returnDate,
                                    'updated_at' => $returnDate,
                                ]);

                                // Add items to Goods Return (return some rejected items)
                                $returnedItems = 0;
                                foreach ($gr->items as $grItem) {
                                    if ($grItem->quantity_rejected > 0 && $returnedItems < 2) {
                                        GoodsReturnItem::create([
                                            'goods_return_id' => $goodsReturn->id,
                                            'goods_receipt_item_id' => $grItem->id,
                                            'inventory_item_id' => $grItem->inventory_item_id,
                                            'quantity' => $grItem->quantity_rejected,
                                            'reason' => $grItem->rejection_reason ?? 'Defective',
                                        ]);
                                        $returnedItems++;
                                    }
                                }
                                
                                $this->goodsReturnsCreated++;
                                $this->info("        ✓ Created Goods Return: {$goodsReturn->return_number}");
                            }
                        }
                    }
                }
            }

            // Create Material Issuances for active/planning projects (2-3 per project)
            if (($project->status === 'active' || $project->status === 'planning') && $this->getTotalTransactions() < $targetTotal) {
                $issuanceCount = rand(2, 3);
                for ($issIdx = 0; $issIdx < $issuanceCount && $this->getTotalTransactions() < $targetTotal; $issIdx++) {
                    $monthOffset = ($issIdx + 2) % count($months);
                    $targetMonth = $months[$monthOffset];
                    $daysInMonth = Carbon::parse($targetMonth . '-01')->daysInMonth;
                    $randomDay = rand(1, $daysInMonth);
                    $issDate = Carbon::parse($targetMonth . '-' . str_pad($randomDay, 2, '0', STR_PAD_LEFT));
                    $issStatuses = ['draft', 'approved', 'issued', 'completed'];
                    $issStatus = $issStatuses[rand(0, count($issStatuses) - 1)];
                    
                    $issuancePurposes = [
                        'Window and door installation',
                        'Glass panel fabrication',
                        'Aluminum framework assembly',
                        'Modular cabinet installation',
                        'Curtain wall glazing',
                        'Interior partition work',
                        'Exterior facade installation',
                        'UPVC system assembly'
                    ];
                    
                    $issuance = MaterialIssuance::create([
                        'issuance_number' => $this->getNextUniqueNumber('MI', 'material_issuances', 'issuance_number'),
                        'project_id' => $project->id,
                        'work_order_number' => 'WO-' . strtoupper(Str::random(6)),
                        'issuance_type' => 'project',
                        'issuance_date' => $issDate,
                        'status' => $issStatus,
                        'purpose' => $issuancePurposes[$issIdx % count($issuancePurposes)] . " for {$project->name}",
                        'requested_by' => $pm->id,
                        'approved_by' => ($issStatus === 'approved' || $issStatus === 'issued' || $issStatus === 'completed') ? $inventoryManager->id : null,
                        'issued_by' => ($issStatus === 'issued' || $issStatus === 'completed') ? $inventoryManager->id : null,
                        'approved_at' => ($issStatus === 'approved' || $issStatus === 'issued' || $issStatus === 'completed') ? $issDate->copy()->addDays(rand(1, 3)) : null,
                        'issued_at' => ($issStatus === 'issued' || $issStatus === 'completed') ? ($issDate->copy()->addDays(rand(2, 5))) : null,
                        'notes' => "Material issuance for {$project->name}",
                        'created_at' => $issDate,
                        'updated_at' => $issDate,
                    ]);

                    // Add items to Material Issuance
                    $selectedItems = $inventoryItems->random(min(rand(3, 5), $inventoryItems->count()));
                    foreach ($selectedItems as $item) {
                        $latestMovement = StockMovement::where('inventory_item_id', $item->id)
                            ->orderBy('created_at', 'desc')
                            ->first();
                        $currentStock = $latestMovement ? (float) $latestMovement->balance_after : 0;
                        
                        if ($currentStock > 0) {
                            $qtyToIssue = min(rand(10, 80), (int) ($currentStock * 0.4)); // Issue up to 40% of stock
                            
                            MaterialIssuanceItem::create([
                                'material_issuance_id' => $issuance->id,
                                'inventory_item_id' => $item->id,
                                'quantity' => $qtyToIssue,
                                'unit_cost' => $item->unit_cost ?? 0,
                                'notes' => "Issued for {$project->name}",
                            ]);

                            // Create stock movement if issued
                            if ($issStatus === 'issued' || $issStatus === 'completed') {
                                $balanceAfter = max(0, $currentStock - $qtyToIssue);
                                StockMovement::create([
                                    'inventory_item_id' => $item->id,
                                    'movement_type' => 'stock_out',
                                    'reference_type' => 'App\Models\MaterialIssuance',
                                    'reference_id' => $issuance->id,
                                    'quantity' => $qtyToIssue,
                                    'unit_cost' => $item->unit_cost ?? 0,
                                    'balance_after' => $balanceAfter,
                                    'notes' => "Stock out from Material Issuance {$issuance->issuance_number} for {$project->name}",
                                    'created_by' => $inventoryManager->id,
                                    'created_at' => $issuance->issued_at ?? $issDate,
                                    'updated_at' => $issuance->issued_at ?? $issDate,
                                ]);
                            }
                        }
                    }
                    
                    $this->issuancesCreated++;
                    $this->info("  ✓ Created Material Issuance: {$issuance->issuance_number}");
                }
            }

            // Create Change Orders for some projects (about 1 per 4 projects)
            if (($projectIndex % 4 === 0 || rand(1, 100) <= 25) && $this->getTotalTransactions() < $targetTotal) {
                $monthOffset = rand(0, count($months) - 1);
                $targetMonth = $months[$monthOffset];
                $daysInMonth = Carbon::parse($targetMonth . '-01')->daysInMonth;
                $randomDay = rand(1, $daysInMonth);
                $coDate = Carbon::parse($targetMonth . '-' . str_pad($randomDay, 2, '0', STR_PAD_LEFT));
                $coStatuses = ['pending', 'approved', 'rejected'];
                $coStatus = $coStatuses[rand(0, count($coStatuses) - 1)];
                
                $changeOrder = ChangeOrder::create([
                    'project_id' => $project->id,
                    'change_order_number' => $this->getNextUniqueNumber('CO', 'change_orders', 'change_order_number'),
                    'description' => "Change order for {$project->name} - Design modification and scope adjustment",
                    'reason' => "Client requested changes in design specifications and additional features",
                    'additional_days' => rand(5, 30),
                    'additional_cost' => rand(50000, 500000),
                    'status' => $coStatus,
                    'requested_by' => $pm->id,
                    'approved_by' => ($coStatus === 'approved') ? $adminUser->id : null,
                    'approved_at' => ($coStatus === 'approved') ? $coDate->copy()->addDays(rand(1, 5)) : null,
                    'approval_notes' => ($coStatus === 'approved') ? 'Approved as requested by client' : null,
                    'created_at' => $coDate,
                    'updated_at' => $coDate,
                ]);
                
                $this->changeOrdersCreated++;
                $this->info("  ✓ Created Change Order: {$changeOrder->change_order_number}");
            }
        }

        $totalTransactions = $this->getTotalTransactions();
        
        $this->info("\n✅ Transaction seeding completed!");
        $this->info("📊 Summary:");
        $this->info("   - Completed Projects: {$this->completedProjectsCreated}");
        $this->info("   - Purchase Requests: {$this->prsCreated}");
        $this->info("   - Quotations: {$this->quotationsCreated}");
        $this->info("   - Purchase Orders: {$this->posCreated}");
        $this->info("   - Goods Receipts: {$this->grsCreated}");
        $this->info("   - Goods Returns: {$this->goodsReturnsCreated}");
        $this->info("   - Material Issuances: {$this->issuancesCreated}");
        $this->info("   - Change Orders: {$this->changeOrdersCreated}");
        $this->info("   - Total Transactions: {$totalTransactions}");
        $this->info("\n📅 All transactions are properly linked following the system flow:");
        $this->info("   Projects → PR → Quotations → PO → GR → Goods Returns → Material Issuances");
        
        return 0;
    }

    protected function getTotalTransactions(): int
    {
        return $this->prsCreated + $this->quotationsCreated + $this->posCreated + 
               $this->grsCreated + $this->goodsReturnsCreated + $this->issuancesCreated + 
               $this->changeOrdersCreated;
    }
}
