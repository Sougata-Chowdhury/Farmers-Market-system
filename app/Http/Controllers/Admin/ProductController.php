<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:80'],
            'state' => ['nullable', 'in:active,inactive,removed'],
        ]);

        $products = Product::query()
            ->withTrashed()
            ->with(['category:id,name', 'farmer:id,name'])
            ->search($filters['q'] ?? null)
            ->when(
                $filters['category'] ?? null,
                fn ($query, $slug) => $query->whereHas('category', fn ($query) => $query->where('slug', $slug))
            )
            ->when(($filters['state'] ?? null) === 'active', fn ($query) => $query->where('is_active', true)->whereNull('deleted_at'))
            ->when(($filters['state'] ?? null) === 'inactive', fn ($query) => $query->where('is_active', false)->whereNull('deleted_at'))
            ->when(($filters['state'] ?? null) === 'removed', fn ($query) => $query->onlyTrashed())
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
            'filters' => $filters,
        ]);
    }

    public function toggle(Product $product): RedirectResponse
    {
        $product->update(['is_active' => ! $product->is_active]);

        return back()->with('status', $product->is_active
            ? "{$product->name} is now visible in the marketplace."
            : "{$product->name} has been hidden from the marketplace.");
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->update(['is_active' => false]);
        $product->delete();

        return back()->with('status', "{$product->name} removed.");
    }

    public function restore(int $product): RedirectResponse
    {
        $model = Product::withTrashed()->findOrFail($product);
        $model->restore();
        $model->update(['is_active' => true]);

        return back()->with('status', "{$model->name} restored.");
    }
}
