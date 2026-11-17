<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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
        $data = $request->safe()->except('images');
        $data['farmer_id'] = $request->user()->id;
        $data['is_active'] = true;

        $product = Product::create($data);

        $this->storeImages($request, $product);

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

        $product->update($request->safe()->except('images'));

        $this->storeImages($request, $product);

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

    public function destroyMedia(Product $product, Media $media): RedirectResponse
    {
        $this->authorize('update', $product);

        abort_unless(
            $media->model_type === $product->getMorphClass() && $media->model_id === $product->getKey(),
            404
        );

        $media->delete();

        return back()->with('status', 'Photo removed from the gallery.');
    }

    private function storeImages(ProductRequest $request, Product $product): void
    {
        foreach ($request->file('images', []) as $file) {
            $product->addMedia($file)->toMediaCollection('gallery');
        }
    }
}
