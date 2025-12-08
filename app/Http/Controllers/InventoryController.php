<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\StockMovement;
use App\Models\PurchaseRequest;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InventoryController extends Controller
{
    protected $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function index(Request $request)
    {
        $query = InventoryItem::withCount('stockMovements');

        if ($request->has('item_type')) {
            $query->where('item_type', $request->item_type);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('item_code', 'like', "%{$search}%");
            });
        }

        $items = $query->latest()->paginate(10);

        // Add current stock to each item
        $items->getCollection()->transform(function ($item) {
            if ($item->id) {
                $item->current_stock = $this->stockService->getCurrentStock($item->id);
                $item->needs_reorder = $this->stockService->checkReorderLevel($item->id);
            } else {
                $item->current_stock = 0;
                $item->needs_reorder = false;
            }
            return $item;
        });

        return view('inventory.index', compact('items'));
    }

    public function create()
    {
        return view('inventory.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string',
            'unit_of_measure' => 'required|string',
            'unit_cost' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
            'reorder_quantity' => 'nullable|numeric|min:0',
            'item_type' => 'required|in:raw_material,finished_good,consumable,tool',
            'status' => 'required|in:active,inactive',
        ]);

        $validated['item_code'] = 'ITM-' . strtoupper(Str::random(8));
        
        // Ensure unit_cost defaults to 0 if null or empty
        if (!isset($validated['unit_cost']) || $validated['unit_cost'] === null || $validated['unit_cost'] === '') {
            $validated['unit_cost'] = 0;
        }

        $item = InventoryItem::create($validated);

        return redirect()->route('inventory.show', $item)->with('success', 'Inventory item created successfully.');
    }

    public function show(InventoryItem $inventory)
    {
        // Ensure the inventory item exists and has an ID
        if (!$inventory || !$inventory->id) {
            abort(404, 'Inventory item not found.');
        }

        $currentStock = $this->stockService->getCurrentStock($inventory->id);

        // Load related purchase requests
        $purchaseRequests = PurchaseRequest::whereHas('items', function($query) use ($inventory) {
            $query->where('inventory_item_id', $inventory->id);
        })
        ->with(['items' => function($query) use ($inventory) {
            $query->where('inventory_item_id', $inventory->id);
        }, 'project', 'requestedBy'])
        ->orderBy('created_at', 'desc')
        ->paginate(10, ['*'], 'pr_page');

        return view('inventory.show', compact('inventory', 'currentStock', 'purchaseRequests'));
    }

    public function edit(InventoryItem $inventory)
    {
        if (!$inventory || !$inventory->id) {
            abort(404, 'Inventory item not found.');
        }
        return view('inventory.edit', compact('inventory'));
    }

    public function update(Request $request, InventoryItem $inventory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string',
            'unit_of_measure' => 'required|string',
            'unit_cost' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
            'reorder_quantity' => 'nullable|numeric|min:0',
            'item_type' => 'required|in:raw_material,finished_good,consumable,tool',
            'status' => 'required|in:active,inactive',
        ]);

        // Ensure unit_cost defaults to 0 if null or empty
        if (!isset($validated['unit_cost']) || $validated['unit_cost'] === null || $validated['unit_cost'] === '') {
            $validated['unit_cost'] = 0;
        }

        $inventory->update($validated);

        return redirect()->route('inventory.show', $inventory)->with('success', 'Inventory item updated successfully.');
    }

    public function adjustStock(Request $request, InventoryItem $inventoryItem)
    {
        // Ensure the inventory item has an ID
        if (!$inventoryItem->id) {
            abort(404, 'Inventory item not found.');
        }

        $validated = $request->validate([
            'quantity' => 'required|numeric',
            'type' => 'required|in:adjustment_in,adjustment_out',
            'notes' => 'nullable|string',
        ]);

        $this->stockService->adjustStock($inventoryItem->id, $validated['quantity'], $validated['type'], $validated['notes'] ?? null);

        return redirect()->route('inventory.show', $inventoryItem)->with('success', 'Stock adjusted successfully.');
    }
}

