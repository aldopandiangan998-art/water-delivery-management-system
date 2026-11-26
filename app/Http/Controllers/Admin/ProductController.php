<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')->latest();

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->paginate(12)->withQueryString();
        $categories = ProductCategory::where('is_active', true)->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = ProductCategory::where('is_active', true)->get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:product_categories,id'],
            'unit' => ['required', 'string', 'max:50'],
            'price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'is_returnable_gallon' => ['nullable', 'boolean'],
            'deposit_price' => ['nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'low_stock_threshold' => ['required', 'integer', 'min:0'],
            'tracks_inventory' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $slug = Str::slug($validated['name']) . '-' . rand(100, 999);

        $product = Product::create([
            'category_id' => $validated['category_id'] ?? null,
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'image' => $imagePath,
            'unit' => $validated['unit'],
            'price' => $validated['price'],
            'cost_price' => $validated['cost_price'] ?? 0,
            'is_returnable_gallon' => $request->boolean('is_returnable_gallon'),
            'deposit_price' => $validated['deposit_price'] ?? 0,
            'tracks_inventory' => $request->boolean('tracks_inventory', true),
            'stock_quantity' => $validated['stock_quantity'],
            'low_stock_threshold' => $validated['low_stock_threshold'],
            'is_active' => true,
        ]);

        if ($product->stock_quantity > 0) {
            StockMovement::create([
                'product_id' => $product->id,
                'type' => 'in',
                'quantity' => $product->stock_quantity,
                'stock_before' => 0,
                'stock_after' => $product->stock_quantity,
                'reference_type' => 'Product Created',
                'reference_id' => $product->id,
                'notes' => 'Stok awal penambahan produk baru',
                'created_by' => Auth::id(),
            ]);
        }

        return redirect()->route('admin.products.index')->with('success', "Produk {$product->name} berhasil ditambahkan.");
    }

    public function edit(Product $product)
    {
        $categories = ProductCategory::where('is_active', true)->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:product_categories,id'],
            'unit' => ['required', 'string', 'max:50'],
            'price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'is_returnable_gallon' => ['nullable', 'boolean'],
            'deposit_price' => ['nullable', 'numeric', 'min:0'],
            'low_stock_threshold' => ['required', 'integer', 'min:0'],
            'tracks_inventory' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
            $product->image = $imagePath;
        }

        $product->update([
            'name' => $validated['name'],
            'category_id' => $validated['category_id'] ?? null,
            'unit' => $validated['unit'],
            'price' => $validated['price'],
            'cost_price' => $validated['cost_price'] ?? 0,
            'is_returnable_gallon' => $request->boolean('is_returnable_gallon'),
            'deposit_price' => $validated['deposit_price'] ?? 0,
            'low_stock_threshold' => $validated['low_stock_threshold'],
            'tracks_inventory' => $request->boolean('tracks_inventory', true),
            'is_active' => $request->boolean('is_active', true),
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('admin.products.index')->with('success', "Produk {$product->name} berhasil diperbarui.");
    }

    public function adjustStock(Request $request, Product $product)
    {
        $validated = $request->validate([
            'type' => ['required', 'in:in,out,adjustment'],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($product, $validated) {
            $stockBefore = $product->stock_quantity;
            $qty = $validated['quantity'];

            if ($validated['type'] === 'in') {
                $stockAfter = $stockBefore + $qty;
                $movementQty = $qty;
            } elseif ($validated['type'] === 'out') {
                $stockAfter = max(0, $stockBefore - $qty);
                $movementQty = -$qty;
            } else { // adjustment to set exact
                $stockAfter = $qty;
                $movementQty = $stockAfter - $stockBefore;
            }

            $product->update(['stock_quantity' => $stockAfter]);

            StockMovement::create([
                'product_id' => $product->id,
                'type' => $validated['type'],
                'quantity' => $movementQty,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reference_type' => 'Manual Adjustment',
                'reference_id' => null,
                'notes' => $validated['notes'],
                'created_by' => Auth::id(),
            ]);
        });

        return back()->with('success', "Stok {$product->name} berhasil disesuaikan.");
    }

    public function destroy(Product $product)
    {
        if ($product->orderItems()->exists()) {
            return back()->with('error', "Produk {$product->name} tidak dapat dihapus karena sudah memiliki riwayat pesanan. Anda dapat menonaktifkan status produk ini.");
        }

        $productName = $product->name;
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', "Produk {$productName} berhasil dihapus.");
    }
}
