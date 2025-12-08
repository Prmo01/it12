<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $suppliers = $query->latest()->paginate(10);

        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'tax_id' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        $validated['code'] = 'SUP-' . strtoupper(Str::random(8));

        $supplier = Supplier::create($validated);

        return redirect()->route('suppliers.show', $supplier)->with('success', 'Supplier created successfully.');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load(['purchaseOrders', 'quotations', 'prices.inventoryItem']);
        $inventoryItems = \App\Models\InventoryItem::where('status', 'active')->orderBy('name')->get();
        return view('suppliers.show', compact('supplier', 'inventoryItems'));
    }

    public function storePrice(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'unit_price' => 'nullable|numeric|min:0',
            'effective_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:effective_date',
            'notes' => 'nullable|string',
        ]);

        // Ensure unit_price defaults to 0 if null or empty
        if (!isset($validated['unit_price']) || $validated['unit_price'] === null || $validated['unit_price'] === '') {
            $validated['unit_price'] = 0;
        }

        \App\Models\SupplierPrice::updateOrCreate(
            [
                'supplier_id' => $supplier->id,
                'inventory_item_id' => $validated['inventory_item_id'],
            ],
            [
                'unit_price' => $validated['unit_price'] ?? 0,
                'effective_date' => $validated['effective_date'] ?? now(),
                'expiry_date' => $validated['expiry_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]
        );

        return redirect()->back()->with('success', 'Supplier price updated successfully.');
    }

    public function updatePrice(Request $request, Supplier $supplier, $priceId)
    {
        $price = \App\Models\SupplierPrice::where('supplier_id', $supplier->id)
            ->where('id', $priceId)
            ->firstOrFail();

        $validated = $request->validate([
            'unit_price' => 'nullable|numeric|min:0',
            'effective_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:effective_date',
            'notes' => 'nullable|string',
        ]);

        // Ensure unit_price defaults to 0 if null or empty
        if (!isset($validated['unit_price']) || $validated['unit_price'] === null || $validated['unit_price'] === '') {
            $validated['unit_price'] = 0;
        }

        $price->update([
            'unit_price' => $validated['unit_price'],
            'effective_date' => $validated['effective_date'] ?? $price->effective_date,
            'expiry_date' => $validated['expiry_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Supplier price updated successfully.');
    }

    public function deletePrice(Supplier $supplier, $priceId)
    {
        $price = \App\Models\SupplierPrice::where('supplier_id', $supplier->id)
            ->where('id', $priceId)
            ->firstOrFail();
        
        $price->delete();

        return redirect()->back()->with('success', 'Supplier price deleted successfully.');
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'tax_id' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        $supplier->update($validated);

        return redirect()->route('suppliers.show', $supplier)->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted successfully.');
    }
}

