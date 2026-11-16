<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = $request->user()
            ->products()
            ->with('category:id,name')
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->latest()
            ->paginate(10);

        return view('farmer.products.index', compact('products'));
    }

    public function create(): View
    {
        return view('farmer.products.create', [
            'product' => new Product,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('image');
        $data['farmer_id'] = $request->user()->id;
        $data['image_path'] = $this->storeImage($request);
        $data['is_active'] = true;

        Product::create($data);

        return redirect()
            ->route('farmer.products.index')
            ->with('status', 'Product published to the marketplace.');
    }

    public function edit(Product $product): View
    {
        $this->authorize('update', $product);

        return view('farmer.products.edit', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $data = $request->safe()->except('image');

        if ($path = $this->storeImage($request)) {
            $this->deleteImage($product->image_path);
            $data['image_path'] = $path;
        }

        $product->update($data);

        return redirect()
            ->route('farmer.products.index')
            ->with('status', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $product->update(['is_active' => false]);
        $product->delete();

        return redirect()
            ->route('farmer.products.index')
            ->with('status', 'Product removed from the marketplace.');
    }

    private function storeImage(ProductRequest $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        return $request->file('image')->store('products', 'public');
    }

    private function deleteImage(?string $path): void
    {
        if ($path && ! Str::startsWith($path, ['http://', 'https://'])) {
            Storage::disk('public')->delete($path);
        }
    }
}
