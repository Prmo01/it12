<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Project;
use App\Models\Supplier;
use App\Models\InventoryItem;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockMovement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create or Update Roles
        $roles = [
            ['name' => 'Admin', 'slug' => 'admin', 'description' => 'Full system access - Can manage all modules, users, and system settings'],
            ['name' => 'Inventory Manager', 'slug' => 'inventory_manager', 'description' => 'Manages inventory, goods receipts, goods returns, and material issuance'],
            ['name' => 'Purchasing', 'slug' => 'purchasing', 'description' => 'Handles procurement - Purchase requests, quotations, purchase orders, and suppliers'],
            ['name' => 'Project Manager', 'slug' => 'project_manager', 'description' => 'Manages projects and change orders'],
            ['name' => 'Warehouse Manager', 'slug' => 'warehouse_manager', 'description' => 'Manages warehouse operations, inspects goods before approval and flags defective items'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['slug' => $role['slug']],
                $role
            );
        }

        // Remove unwanted roles
        $unwantedRoleSlugs = [
            'fabrication',
            'finance',
            'warehouse',
            'warehouse_staff',
            'production_manager',
            'purchasing_assistant',
            'executive',
            'executive_management',
            'quality_control',
            'viewer',
        ];

        foreach ($unwantedRoleSlugs as $slug) {
            Role::where('slug', $slug)->delete();
        }

        // Create Users for all roles
        $usersToCreate = [
            [
                'email' => 'admin@erp.com',
                'name' => 'Admin User',
                'role_slug' => 'admin',
            ],
            [
                'email' => 'inventory@erp.com',
                'name' => 'Inventory Manager',
                'role_slug' => 'inventory_manager',
            ],
            [
                'email' => 'purchasing@erp.com',
                'name' => 'Purchasing Officer',
                'role_slug' => 'purchasing',
            ],
            [
                'email' => 'pm@erp.com',
                'name' => 'Project Manager',
                'role_slug' => 'project_manager',
            ],
            [
                'email' => 'warehouse@erp.com',
                'name' => 'Warehouse Manager',
                'role_slug' => 'warehouse_manager',
            ],
        ];

        $pm = null;
        foreach ($usersToCreate as $userData) {
            $role = Role::where('slug', $userData['role_slug'])->first();
            if ($role) {
                $user = User::updateOrCreate(
                    ['email' => $userData['email']],
                    [
                        'name' => $userData['name'],
                        'password' => Hash::make('password'), // Default password for all users
                        'role_id' => $role->id,
                    ]
                );
                
                // Store PM for later use in project creation
                if ($userData['role_slug'] === 'project_manager') {
                    $pm = $user;
                }
            }
        }

        // Create Suppliers
        $suppliers = [
            ['name' => 'Premium Aluminum Distributors', 'contact_person' => 'Michael Santos', 'email' => 'sales@premiumaluminum.com', 'phone' => '0917-123-4567'],
            ['name' => 'Glass Works Philippines', 'contact_person' => 'Maria Garcia', 'email' => 'info@glassworksph.com', 'phone' => '0918-234-5678'],
            ['name' => 'UPVC Solutions Inc.', 'contact_person' => 'Robert Tan', 'email' => 'contact@upvcsolutions.com', 'phone' => '0919-345-6789'],
            ['name' => 'Cabinet Hardware Supply', 'contact_person' => 'Jennifer Lee', 'email' => 'sales@cabinetware.com', 'phone' => '0920-456-7890'],
            ['name' => 'Wood Materials Depot', 'contact_person' => 'Carlos Rodriguez', 'email' => 'info@wooddepot.com', 'phone' => '0921-567-8901'],
            ['name' => 'Industrial Fasteners Co.', 'contact_person' => 'Anna Martinez', 'email' => 'sales@fastenersph.com', 'phone' => '0922-678-9012'],
            ['name' => 'Sealants & Adhesives Pro', 'contact_person' => 'James Wilson', 'email' => 'contact@sealantspro.com', 'phone' => '0923-789-0123'],
            ['name' => 'Metal Fabrication Supplies', 'contact_person' => 'Sarah Chen', 'email' => 'info@metalfab.com', 'phone' => '0924-890-1234'],
        ];

        foreach ($suppliers as $supplier) {
            $existing = Supplier::where('email', $supplier['email'])->first();
            if ($existing) {
                // Update existing supplier without changing code
                $existing->update(array_merge($supplier, ['status' => 'active']));
            } else {
                // Create new supplier with code
                Supplier::create(array_merge($supplier, [
                    'code' => 'SUP-' . strtoupper(Str::random(8)),
                    'status' => 'active',
                ]));
            }
        }

        // Create Inventory Items
        $items = [
            // Aluminum Materials
            ['name' => 'Aluminum Profile 1.2mm x 50mm', 'item_type' => 'raw_material', 'unit_of_measure' => 'pcs', 'unit_cost' => 0, 'category' => 'Aluminum'],
            ['name' => 'Aluminum Profile 1.4mm x 76mm', 'item_type' => 'raw_material', 'unit_of_measure' => 'pcs', 'unit_cost' => 0, 'category' => 'Aluminum'],
            ['name' => 'Aluminum Sheet 1.2mm x 4x8', 'item_type' => 'raw_material', 'unit_of_measure' => 'pcs', 'unit_cost' => 0, 'category' => 'Aluminum'],
            ['name' => 'Aluminum Rivets 4mm', 'item_type' => 'consumable', 'unit_of_measure' => 'pcs', 'unit_cost' => 0, 'category' => 'Aluminum'],
            ['name' => 'Aluminum Screws 5mm', 'item_type' => 'consumable', 'unit_of_measure' => 'pcs', 'unit_cost' => 0, 'category' => 'Aluminum'],
            ['name' => 'Aluminum Sealant', 'item_type' => 'consumable', 'unit_of_measure' => 'tubes', 'unit_cost' => 0, 'category' => 'Aluminum'],
            
            // Glass Materials
            ['name' => 'Clear Glass 5mm', 'item_type' => 'raw_material', 'unit_of_measure' => 'sqm', 'unit_cost' => 0, 'category' => 'Glass'],
            ['name' => 'Clear Glass 6mm', 'item_type' => 'raw_material', 'unit_of_measure' => 'sqm', 'unit_cost' => 0, 'category' => 'Glass'],
            ['name' => 'Tempered Glass 5mm', 'item_type' => 'raw_material', 'unit_of_measure' => 'sqm', 'unit_cost' => 0, 'category' => 'Glass'],
            ['name' => 'Tempered Glass 6mm', 'item_type' => 'raw_material', 'unit_of_measure' => 'sqm', 'unit_cost' => 0, 'category' => 'Glass'],
            ['name' => 'Laminated Glass 5mm', 'item_type' => 'raw_material', 'unit_of_measure' => 'sqm', 'unit_cost' => 0, 'category' => 'Glass'],
            ['name' => 'Glass Polishing Compound', 'item_type' => 'consumable', 'unit_of_measure' => 'kg', 'unit_cost' => 0, 'category' => 'Glass'],
            
            // UPVC Materials
            ['name' => 'UPVC Profile 58mm White', 'item_type' => 'raw_material', 'unit_of_measure' => 'pcs', 'unit_cost' => 0, 'category' => 'UPVC'],
            ['name' => 'UPVC Profile 80mm White', 'item_type' => 'raw_material', 'unit_of_measure' => 'pcs', 'unit_cost' => 0, 'category' => 'UPVC'],
            ['name' => 'UPVC Gasket 5mm', 'item_type' => 'consumable', 'unit_of_measure' => 'meters', 'unit_cost' => 0, 'category' => 'UPVC'],
            ['name' => 'UPVC Hardware Set', 'item_type' => 'consumable', 'unit_of_measure' => 'sets', 'unit_cost' => 0, 'category' => 'UPVC'],
            ['name' => 'UPVC Screws', 'item_type' => 'consumable', 'unit_of_measure' => 'pcs', 'unit_cost' => 0, 'category' => 'UPVC'],
            ['name' => 'UPVC Sealant', 'item_type' => 'consumable', 'unit_of_measure' => 'tubes', 'unit_cost' => 0, 'category' => 'UPVC'],
            
            // Modular Cabinet Materials
            ['name' => 'Plywood 18mm', 'item_type' => 'raw_material', 'unit_of_measure' => 'pcs', 'unit_cost' => 0, 'category' => 'Modular Cabinet'],
            ['name' => 'Plywood 12mm', 'item_type' => 'raw_material', 'unit_of_measure' => 'pcs', 'unit_cost' => 0, 'category' => 'Modular Cabinet'],
            ['name' => 'MDF Board 18mm', 'item_type' => 'raw_material', 'unit_of_measure' => 'pcs', 'unit_cost' => 0, 'category' => 'Modular Cabinet'],
            ['name' => 'Cabinet Hinges Soft Close', 'item_type' => 'consumable', 'unit_of_measure' => 'pairs', 'unit_cost' => 0, 'category' => 'Modular Cabinet'],
            ['name' => 'Cabinet Handles Chrome', 'item_type' => 'consumable', 'unit_of_measure' => 'pcs', 'unit_cost' => 0, 'category' => 'Modular Cabinet'],
            ['name' => 'Drawer Slides 450mm', 'item_type' => 'consumable', 'unit_of_measure' => 'pairs', 'unit_cost' => 0, 'category' => 'Modular Cabinet'],
            ['name' => 'Wood Screws 3.5mm x 40mm', 'item_type' => 'consumable', 'unit_of_measure' => 'pcs', 'unit_cost' => 0, 'category' => 'Modular Cabinet'],
            ['name' => 'Wood Glue', 'item_type' => 'consumable', 'unit_of_measure' => 'bottles', 'unit_cost' => 0, 'category' => 'Modular Cabinet'],
            ['name' => 'Cabinet Laminates', 'item_type' => 'raw_material', 'unit_of_measure' => 'sheets', 'unit_cost' => 0, 'category' => 'Modular Cabinet'],
            
            // General Hardware & Tools
            ['name' => 'Silicone Sealant White', 'item_type' => 'consumable', 'unit_of_measure' => 'tubes', 'unit_cost' => 0, 'category' => 'Hardware'],
            ['name' => 'Silicone Sealant Clear', 'item_type' => 'consumable', 'unit_of_measure' => 'tubes', 'unit_cost' => 0, 'category' => 'Hardware'],
            ['name' => 'Drill Bits Set', 'item_type' => 'consumable', 'unit_of_measure' => 'sets', 'unit_cost' => 0, 'category' => 'Hardware'],
            ['name' => 'Screws Assorted', 'item_type' => 'consumable', 'unit_of_measure' => 'pcs', 'unit_cost' => 0, 'category' => 'Hardware'],
            ['name' => 'Anchors 8mm', 'item_type' => 'consumable', 'unit_of_measure' => 'pcs', 'unit_cost' => 0, 'category' => 'Hardware'],
        ];

        foreach ($items as $item) {
            // Check for existing item (excluding soft-deleted)
            $existing = InventoryItem::where('name', $item['name'])
                ->whereNull('deleted_at')
                ->first();
            
            if ($existing) {
                // Update existing item without changing item_code
                $existing->update(array_merge($item, [
                    'status' => 'active',
                    'reorder_level' => 10,
                    'reorder_quantity' => 50,
                ]));
            } else {
                // Create new item with item_code
                InventoryItem::create(array_merge($item, [
                    'item_code' => 'ITM-' . strtoupper(Str::random(8)),
                    'status' => 'active',
                    'reorder_level' => 10,
                    'reorder_quantity' => 50,
                ]));
            }
        }

        // Get users
        $adminRole = Role::where('slug', 'admin')->first();
        $adminUser = $adminRole ? $adminRole->users()->first() : null;
        $purchasingUser = User::whereHas('role', function($q) {
            $q->where('slug', 'purchasing');
        })->first();

        // Create 20 Projects (only if PM exists)
        $projects = [];
        if ($pm) {
            $projectStatuses = ['planning', 'active', 'active', 'on_hold', 'active']; // More active projects
            for ($i = 1; $i <= 20; $i++) {
                $projectName = "Construction Project {$i}";
                $status = $projectStatuses[$i % count($projectStatuses)];
                
                $project = Project::updateOrCreate(
                    [
                        'project_code' => 'PRJ-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                    ],
                    [
                        'name' => $projectName,
                        'description' => "Construction project for {$projectName} - Phase " . ($i % 3 + 1),
                        'project_manager_id' => $pm->id,
                        'start_date' => now()->subDays(rand(0, 60))->addDays($i * 5),
                        'end_date' => now()->addDays($i * 5 + rand(60, 180)),
                        'status' => $status,
                        'budget' => rand(100000, 5000000),
                        'progress_percentage' => $status === 'active' ? rand(10, 80) : ($status === 'on_hold' ? rand(0, 50) : 0),
                    ]
                );
                $projects[] = $project;
            }
        }

        // Create 15 Purchase Requests
        $purchaseRequests = [];
        if ($pm && $adminUser && count($projects) > 0) {
            $prStatuses = ['draft', 'submitted', 'approved', 'approved', 'approved'];
            for ($i = 1; $i <= 15; $i++) {
                $project = $projects[array_rand($projects)];
                $status = $prStatuses[$i % count($prStatuses)];
                
                $pr = PurchaseRequest::create([
                    'pr_number' => 'PR-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                    'project_id' => $project->id,
                    'purpose' => "Purchase request #{$i} for project materials",
                    'status' => $status,
                    'requested_by' => $pm->id,
                    'approved_by' => ($status === 'approved') ? $adminUser->id : null,
                    'approved_at' => ($status === 'approved') ? now()->subDays(rand(1, 30)) : null,
                ]);

                // Add 2-5 items to each PR
                $inventoryItems = InventoryItem::inRandomOrder()->take(rand(2, 5))->get();
                foreach ($inventoryItems as $invItem) {
                    PurchaseRequestItem::create([
                        'purchase_request_id' => $pr->id,
                        'inventory_item_id' => $invItem->id,
                        'quantity' => rand(10, 200),
                        'unit_cost' => rand(100, 5000) / 100, // Random price between 1.00 and 50.00
                        'specifications' => "Standard specifications for {$invItem->name}",
                    ]);
                }
                $purchaseRequests[] = $pr;
            }
        }

        // Create 10 Quotations
        $quotations = [];
        if ($purchasingUser && count($purchaseRequests) > 0) {
            $suppliers = Supplier::all();
            $quotationStatuses = ['pending', 'pending', 'accepted', 'accepted', 'rejected'];
            
            for ($i = 1; $i <= 10; $i++) {
                $pr = $purchaseRequests[array_rand($purchaseRequests)];
                $supplier = $suppliers->random();
                $status = $quotationStatuses[$i % count($quotationStatuses)];
                
                $quotation = Quotation::create([
                    'quotation_number' => 'QT-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                    'project_code' => $pr->project->project_code ?? 'PRJ-000001',
                    'purchase_request_id' => $pr->id,
                    'supplier_id' => $supplier->id,
                    'quotation_date' => now()->subDays(rand(1, 20)),
                    'valid_until' => now()->addDays(rand(15, 45)),
                    'status' => $status,
                    'terms_conditions' => 'Standard payment terms: Net 30 days',
                    'notes' => "Quotation {$i} for purchase request {$pr->pr_number}",
                ]);

                // Add items to quotation based on PR items
                $prItems = $pr->items;
                $totalAmount = 0;
                foreach ($prItems as $prItem) {
                    $unitPrice = $prItem->unit_cost * (1 + (rand(-10, 20) / 100)); // ±20% price variation
                    $totalPrice = $unitPrice * $prItem->quantity;
                    $totalAmount += $totalPrice;
                    
                    QuotationItem::create([
                        'quotation_id' => $quotation->id,
                        'inventory_item_id' => $prItem->inventory_item_id,
                        'supplier_id' => $supplier->id,
                        'quantity' => $prItem->quantity,
                        'unit_price' => round($unitPrice, 2),
                        'total_price' => round($totalPrice, 2),
                        'specifications' => $prItem->specifications ?? "Standard specifications",
                    ]);
                }
                
                $quotation->update(['total_amount' => round($totalAmount, 2)]);
                $quotations[] = $quotation;
            }
        }

        // Create 10 Purchase Orders
        $purchaseOrders = [];
        if ($purchasingUser && $adminUser && count($quotations) > 0) {
            $poStatuses = ['draft', 'pending', 'approved', 'approved', 'completed'];
            
            for ($i = 1; $i <= 10; $i++) {
                // Get accepted quotations or any quotation
                $acceptedQuotations = Quotation::where('status', 'accepted')->get();
                $availableQuotations = $acceptedQuotations->count() > 0 ? $acceptedQuotations : collect($quotations);
                $quotation = $availableQuotations->random();
                $pr = $quotation->purchaseRequest;
                $status = $poStatuses[$i % count($poStatuses)];
                
                $po = PurchaseOrder::create([
                    'po_number' => 'PO-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                    'project_code' => $pr->project->project_code ?? 'PRJ-000001',
                    'purchase_request_id' => $pr->id,
                    'quotation_id' => $quotation->id,
                    'supplier_id' => $quotation->supplier_id,
                    'po_date' => now()->subDays(rand(1, 15)),
                    'expected_delivery_date' => now()->addDays(rand(7, 30)),
                    'status' => $status,
                    'delivery_address' => 'Main Warehouse, 123 Industrial St., Davao City',
                    'terms_conditions' => 'Standard delivery terms apply',
                    'created_by' => $purchasingUser->id,
                    'approved_by' => ($status === 'approved' || $status === 'completed') ? $adminUser->id : null,
                    'approved_at' => ($status === 'approved' || $status === 'completed') ? now()->subDays(rand(1, 10)) : null,
                ]);

                // Add items to PO based on quotation items
                $quotationItems = $quotation->items;
                $subtotal = 0;
                foreach ($quotationItems as $qItem) {
                    $totalPrice = $qItem->total_price;
                    $subtotal += $totalPrice;
                    
                    PurchaseOrderItem::create([
                        'purchase_order_id' => $po->id,
                        'inventory_item_id' => $qItem->inventory_item_id,
                        'supplier_id' => $qItem->supplier_id,
                        'quantity' => $qItem->quantity,
                        'unit_price' => $qItem->unit_price,
                        'total_price' => $totalPrice,
                        'specifications' => $qItem->specifications ?? "Standard specifications",
                    ]);
                }
                
                $taxAmount = $subtotal * 0.12; // 12% tax
                $totalAmount = $subtotal + $taxAmount;
                
                $po->update([
                    'subtotal' => round($subtotal, 2),
                    'tax_amount' => round($taxAmount, 2),
                    'total_amount' => round($totalAmount, 2),
                ]);
                $purchaseOrders[] = $po;
            }
        }

        // Add 50 stocks to each inventory item
        $inventoryItems = InventoryItem::all();
        $inventoryManager = User::whereHas('role', function($q) {
            $q->where('slug', 'inventory_manager');
        })->first() ?? $adminUser;

        if ($inventoryManager && count($inventoryItems) > 0) {
            foreach ($inventoryItems as $item) {
                // Get current stock balance
                $latestMovement = StockMovement::where('inventory_item_id', $item->id)
                    ->orderBy('created_at', 'desc')
                    ->first();
                $currentStock = $latestMovement ? (float) $latestMovement->balance_after : 0;
                $balanceAfter = $currentStock + 50;

                // Create stock movement directly
                StockMovement::create([
                    'inventory_item_id' => $item->id,
                    'movement_type' => 'adjustment_in',
                    'reference_type' => null,
                    'reference_id' => null,
                    'quantity' => 50,
                    'unit_cost' => 0,
                    'balance_after' => $balanceAfter,
                    'notes' => "Initial stock seeding - 50 units",
                    'created_by' => $inventoryManager->id,
                ]);
            }
        }
    }
}
