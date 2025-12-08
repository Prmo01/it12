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
    protected $signature = 'transactions:seed {--per-project=20 : Minimum transactions per project}';
    protected $description = 'Create transactions for existing projects (at least 20 per project) spread across different months';

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
        
        // Ensure uniqueness by checking if it exists and incrementing
        while (\DB::table($table)->where($numberColumn, $prefix . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT))->exists()) {
            $nextNumber++;
        }
        
        return $prefix . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    public function handle()
    {
        $transactionsPerProject = (int) $this->option('per-project');
        $this->info("Creating at least {$transactionsPerProject} transactions per existing project...");

        // Get users
        $adminUser = User::whereHas('role', fn($q) => $q->where('slug', 'admin'))->first();
        $pm = User::whereHas('role', fn($q) => $q->where('slug', 'project_manager'))->first();
        $purchasingUser = User::whereHas('role', fn($q) => $q->where('slug', 'purchasing'))->first();
        $inventoryManager = User::whereHas('role', fn($q) => $q->where('slug', 'inventory_manager'))->first();

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

        $projectNames = [
            'Residential Tower A', 'Commercial Complex B', 'Office Building C', 'Mixed-Use Development D',
            'Hotel & Resort E', 'Shopping Mall F', 'Apartment Complex G', 'Warehouse Facility H',
            'Industrial Plant I', 'Hospital Wing J', 'School Campus K', 'Bridge Project L',
            'Highway Extension M', 'Park Renovation N', 'Stadium O', 'Convention Center P',
            'Luxury Condominium Q', 'Townhouse Project R', 'Villa Development S', 'Retail Strip T',
            'Data Center U', 'Manufacturing Hub V', 'Logistics Center W', 'Distribution Hub X',
            'Business Park Y', 'Tech Campus Z', 'Innovation Center AA', 'Research Facility AB',
            'Healthcare Complex AC', 'Senior Living AD', 'Student Housing AE', 'Sports Complex AF',
            'Entertainment Venue AG', 'Cultural Center AH', 'Museum Expansion AI', 'Library Project AJ',
            'Government Building AK', 'Courthouse AL', 'Police Station AM', 'Fire Station AN',
            'Water Treatment AO', 'Power Plant AP', 'Solar Farm AQ', 'Wind Farm AR',
            'Telecom Tower AS', 'Fiber Network AT', 'Smart City AU', 'Sustainable Community AV',
            'Green Building AW', 'LEED Project AX', 'Eco Village AY', 'Conservation Site AZ',
            'Heritage Restoration BA', 'Historic Renovation BB', 'Modern Retrofit BC', 'Facade Upgrade BD'
        ];

        // Generate dates across last 6 months (ensuring distribution)
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $months[] = now()->subMonths($i)->format('Y-m');
        }
        
        $this->info("Distributing transactions across months: " . implode(', ', $months));

        $prsCreated = 0;
        $quotationsCreated = 0;
        $posCreated = 0;
        $grsCreated = 0;
        $issuancesCreated = 0;
        $changeOrdersCreated = 0;


        // Get all existing projects
        $existingProjects = Project::orderBy('created_at')->get();
        
        if ($existingProjects->isEmpty()) {
            $this->error("No existing projects found. Please create projects first.");
            return 1;
        }

        $this->info("Found {$existingProjects->count()} existing projects. Creating transactions for each...");

        // Process each existing project
        foreach ($existingProjects as $projectIndex => $project) {
            $this->info("Processing project: {$project->name} ({$project->project_code})...");
            
            // Get project creation month for date distribution
            $projectMonth = Carbon::parse($project->created_at)->format('Y-m');
            $projectCreatedDate = Carbon::parse($project->created_at);

            // Create Purchase Requests for this project (at least 3-5 per project to reach 20+ transactions)
            $prCount = max(3, rand(3, 5));
            for ($prIdx = 0; $prIdx < $prCount; $prIdx++) {
                // Distribute PR dates across months starting from project creation
                $monthOffset = $prIdx % count($months);
                $targetMonth = $months[$monthOffset];
                $daysInMonth = Carbon::parse($targetMonth . '-01')->daysInMonth;
                $randomDay = rand(1, $daysInMonth);
                $prDate = Carbon::parse($targetMonth . '-' . str_pad($randomDay, 2, '0', STR_PAD_LEFT));
                
                // More approved PRs to generate more transactions
                $prStatuses = ['approved', 'approved', 'approved', 'submitted', 'draft'];
                $prStatus = $prStatuses[$prIdx % count($prStatuses)];
                
                $pr = PurchaseRequest::create([
                    'pr_number' => $this->getNextUniqueNumber('PR', 'purchase_requests', 'pr_number'),
                    'project_id' => $project->id,
                    'purpose' => "Material requisition for {$project->name} - Phase " . ($prIdx + 1),
                    'status' => $prStatus,
                    'requested_by' => $pm->id,
                    'approved_by' => ($prStatus === 'approved') ? $adminUser->id : null,
                    'approved_at' => ($prStatus === 'approved') ? $prDate->copy()->addDays(rand(1, 5)) : null,
                    'created_at' => $prDate,
                    'updated_at' => $prDate,
                ]);

                // Add items to PR
                $itemCount = rand(3, 8);
                $selectedItems = $inventoryItems->random(min($itemCount, $inventoryItems->count()));
                foreach ($selectedItems as $item) {
                    PurchaseRequestItem::create([
                        'purchase_request_id' => $pr->id,
                        'inventory_item_id' => $item->id,
                        'quantity' => rand(20, 500),
                        'unit_cost' => rand(50, 5000) / 100,
                        'specifications' => "Standard specifications for {$item->name}",
                    ]);
                }
                
                $transactions[] = ['type' => 'pr', 'id' => $pr->id, 'date' => $prDate];
                $prsCreated++;

                // Create Quotations for approved PRs
                if ($prStatus === 'approved') {
                    $quoteCount = rand(2, 4); // 2-4 quotations per PR
                    for ($qIdx = 0; $qIdx < $quoteCount; $qIdx++) {
                        $quoteDate = $prDate->copy()->addDays(rand(1, 10));
                        $supplier = $suppliers->random();
                        
                        // Ensure at least one accepted quotation per PR
                        $quoteStatuses = ['accepted', 'accepted', 'pending', 'rejected'];
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
                            'notes' => "Quotation from {$supplier->name} for PR {$pr->pr_number}",
                            'created_at' => $quoteDate,
                            'updated_at' => $quoteDate,
                        ]);

                        // Add items to quotation
                        $totalAmount = 0;
                        foreach ($pr->items as $prItem) {
                            $priceVariation = 0.8 + (rand(0, 40) / 100); // 80% to 120% of PR price
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
                        $transactions[] = ['type' => 'quotation', 'id' => $quotation->id, 'date' => $quoteDate];
                        $quotationsCreated++;

                        // Create Purchase Order from accepted quotation (always create PO for accepted quotes)
                        if ($quoteStatus === 'accepted') {
                            $poDate = $quoteDate->copy()->addDays(rand(2, 7));
                            $poStatuses = ['draft', 'pending', 'approved', 'approved', 'completed'];
                            $poStatus = $poStatuses[rand(0, count($poStatuses) - 1)];
                            
                            $po = PurchaseOrder::create([
                                'po_number' => $this->getNextUniqueNumber('PO', 'purchase_orders', 'po_number'),
                                'project_code' => $project->project_code,
                                'purchase_request_id' => $pr->id,
                                'quotation_id' => $quotation->id,
                                'supplier_id' => $supplier->id,
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
                            foreach ($quotation->items as $qItem) {
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
                            
                            $transactions[] = ['type' => 'po', 'id' => $po->id, 'date' => $poDate];
                            $posCreated++;

                            // Create Goods Receipt for approved/completed POs (create GR for most approved POs)
                            if (($poStatus === 'approved' || $poStatus === 'completed')) {
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
                                    'received_by' => $inventoryManager->id,
                                    'approved_by' => ($grStatus === 'approved') ? $inventoryManager->id : null,
                                    'approved_at' => ($grStatus === 'approved') ? $grDate->copy()->addDays(rand(1, 2)) : null,
                                    'created_at' => $grDate,
                                    'updated_at' => $grDate,
                                ]);

                                // Add items to Goods Receipt
                                foreach ($po->items as $poItem) {
                                    $qtyReceived = $poItem->quantity;
                                    $qtyAccepted = (int) ($qtyReceived * (0.85 + (rand(0, 15) / 100))); // 85-100% acceptance
                                    $qtyRejected = $qtyReceived - $qtyAccepted;
                                    
                                    GoodsReceiptItem::create([
                                        'goods_receipt_id' => $gr->id,
                                        'purchase_order_item_id' => $poItem->id,
                                        'inventory_item_id' => $poItem->inventory_item_id,
                                        'quantity_ordered' => $poItem->quantity,
                                        'quantity_received' => $qtyReceived,
                                        'quantity_accepted' => $qtyAccepted,
                                        'quantity_rejected' => $qtyRejected,
                                        'rejection_reason' => $qtyRejected > 0 ? 'Minor defects' : null,
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
                                            'notes' => "Stock in from GR {$gr->gr_number}",
                                            'created_by' => $inventoryManager->id,
                                            'created_at' => $gr->approved_at ?? $grDate,
                                            'updated_at' => $gr->approved_at ?? $grDate,
                                        ]);
                                    }
                                }
                                
                                $transactions[] = ['type' => 'gr', 'id' => $gr->id, 'date' => $grDate];
                                $grsCreated++;
                            }
                        }
                    }
                }
            }

            // Create Material Issuances for active projects (at least 2-3 per active project)
            if ($project->status === 'active' || $project->status === 'planning') {
                $issuanceCount = max(2, rand(2, 4));
                for ($issIdx = 0; $issIdx < $issuanceCount; $issIdx++) {
                    // Distribute issuances across months
                    $monthOffset = ($issIdx + 1) % count($months);
                    $targetMonth = $months[$monthOffset];
                    $daysInMonth = Carbon::parse($targetMonth . '-01')->daysInMonth;
                    $randomDay = rand(1, $daysInMonth);
                    $issDate = Carbon::parse($targetMonth . '-' . str_pad($randomDay, 2, '0', STR_PAD_LEFT));
                    $issStatuses = ['draft', 'approved', 'issued', 'completed'];
                    $issStatus = $issStatuses[rand(0, count($issStatuses) - 1)];
                    
                    $issuance = MaterialIssuance::create([
                        'issuance_number' => $this->getNextUniqueNumber('MI', 'material_issuances', 'issuance_number'),
                        'project_id' => $project->id,
                        'work_order_number' => 'WO-' . strtoupper(Str::random(6)),
                        'issuance_type' => 'project',
                        'issuance_date' => $issDate,
                        'status' => $issStatus,
                        'purpose' => "Material issuance for {$project->name} - Phase " . ($issIdx + 1),
                        'requested_by' => $pm->id,
                        'approved_by' => ($issStatus === 'approved' || $issStatus === 'issued' || $issStatus === 'completed') ? $inventoryManager->id : null,
                        'issued_by' => ($issStatus === 'issued' || $issStatus === 'completed') ? $inventoryManager->id : null,
                        'approved_at' => ($issStatus === 'approved' || $issStatus === 'issued' || $issStatus === 'completed') ? $issDate->copy()->addDays(rand(1, 3)) : null,
                        'issued_at' => ($issStatus === 'issued' || $issStatus === 'completed') ? ($issDate->copy()->addDays(rand(2, 5))) : null,
                        'notes' => "Materials for construction work",
                        'created_at' => $issDate,
                        'updated_at' => $issDate,
                    ]);

                    // Add items to Material Issuance
                    $selectedItems = $inventoryItems->random(min(rand(3, 6), $inventoryItems->count()));
                    foreach ($selectedItems as $item) {
                        $latestMovement = StockMovement::where('inventory_item_id', $item->id)
                            ->orderBy('created_at', 'desc')
                            ->first();
                        $currentStock = $latestMovement ? (float) $latestMovement->balance_after : 0;
                        
                        if ($currentStock > 0) {
                            $qtyToIssue = min(rand(10, 100), (int) ($currentStock * 0.5)); // Issue up to 50% of stock
                            
                            MaterialIssuanceItem::create([
                                'material_issuance_id' => $issuance->id,
                                'inventory_item_id' => $item->id,
                                'quantity' => $qtyToIssue,
                                'unit_cost' => $item->unit_cost ?? 0,
                                'notes' => "Issued for project use",
                            ]);

                            // Create stock movement if issued
                            if ($issStatus === 'issued') {
                                $balanceAfter = max(0, $currentStock - $qtyToIssue);
                                StockMovement::create([
                                    'inventory_item_id' => $item->id,
                                    'movement_type' => 'stock_out',
                                    'reference_type' => 'App\Models\MaterialIssuance',
                                    'reference_id' => $issuance->id,
                                    'quantity' => $qtyToIssue,
                                    'unit_cost' => $item->unit_cost ?? 0,
                                    'balance_after' => $balanceAfter,
                                    'notes' => "Stock out from Material Issuance {$issuance->issuance_number}",
                                    'created_by' => $inventoryManager->id,
                                    'created_at' => $issuance->issued_at ?? $issDate,
                                    'updated_at' => $issuance->issued_at ?? $issDate,
                                ]);
                            }
                        }
                    }
                    
                    $transactions[] = ['type' => 'issuance', 'id' => $issuance->id, 'date' => $issDate];
                    $issuancesCreated++;
                }
            }

            // Create Change Orders for some projects (at least 1 per 3 projects)
            if (($projectIndex % 3 === 0) || rand(1, 100) <= 40) {
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
                    'description' => "Change order for {$project->name} - Scope modification",
                    'reason' => "Client requested changes in design specifications",
                    'additional_days' => rand(5, 30),
                    'additional_cost' => rand(50000, 500000),
                    'status' => $coStatus,
                    'requested_by' => $pm->id,
                    'approved_by' => ($coStatus === 'approved') ? $adminUser->id : null,
                    'approved_at' => ($coStatus === 'approved') ? $coDate->copy()->addDays(rand(1, 5)) : null,
                    'approval_notes' => ($coStatus === 'approved') ? 'Approved as requested' : null,
                    'created_at' => $coDate,
                    'updated_at' => $coDate,
                ]);
                
                                $transactions[] = ['type' => 'change_order', 'id' => $changeOrder->id, 'date' => $coDate];
                $changeOrdersCreated++;
            }
        }

        $totalTransactions = $prsCreated + $quotationsCreated + $posCreated + $grsCreated + $issuancesCreated + $changeOrdersCreated;
        $avgTransactionsPerProject = $existingProjects->count() > 0 ? round($totalTransactions / $existingProjects->count(), 2) : 0;
        
        $this->info("\n✅ Transaction seeding completed!");
        $this->info("📊 Summary:");
        $this->info("   - Projects Processed: {$existingProjects->count()}");
        $this->info("   - Purchase Requests: {$prsCreated}");
        $this->info("   - Quotations: {$quotationsCreated}");
        $this->info("   - Purchase Orders: {$posCreated}");
        $this->info("   - Goods Receipts: {$grsCreated}");
        $this->info("   - Material Issuances: {$issuancesCreated}");
        $this->info("   - Change Orders: {$changeOrdersCreated}");
        $this->info("   - Total Transactions: {$totalTransactions}");
        $this->info("   - Average Transactions per Project: {$avgTransactionsPerProject}");
        $this->info("\n📅 Transactions spread across last 6 months for trend visualization");
        
        return 0;
    }
}

