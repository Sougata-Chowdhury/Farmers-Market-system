<x-app-layout title="My products">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <x-page-header title="My products" subtitle="Publish, price and restock what you sell.">
            <a href="{{ route('farmer.exports.products') }}" class="btn-secondary">Export CSV</a>
            <a href="{{ route('farmer.products.create') }}" class="btn-primary">Add product</a>
        </x-page-header>

        @if ($products->isEmpty())
            <div class="mt-6">
                <x-empty-state title="No products yet" description="List your first item and it will appear on the marketplace immediately.">
                    <a href="{{ route('farmer.products.create') }}" class="btn-primary">Add your first product</a>
                </x-empty-state>
            </div>
        @else
            <div class="card mt-6 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="table-base">
                        <thead>
                            <tr>
                                <th scope="col">Product</th>
                                <th scope="col">Category</th>
                                <th scope="col" class="text-right">Price</th>
                                <th scope="col" class="text-right">Stock</th>
                                <th scope="col">Status</th>
                                <th scope="col">Rating</th>
                                <th scope="col" class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $product)
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <img src="{{ $product->image_url }}" alt="" onerror="this.onerror=null;this.src='{{ asset('images/product-placeholder.svg') }}';" class="h-12 w-12 rounded-lg object-cover">
                                            <div class="min-w-0">
                                                <p class="truncate font-medium text-slate-900">{{ $product->name }}</p>
                                                <p class="text-xs text-slate-500">Added {{ $product->created_at->format('d M Y') }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $product->category->name }}</td>
                                    <td class="text-right">
                                        <x-money :value="$product->price" />
                                        <span class="text-xs text-slate-400">/{{ $product->unit }}</span>
                                    </td>
                                    <td class="text-right">
                                        <span @class([
                                            'font-semibold',
                                            'text-rose-600' => $product->stock <= 5,
                                            'text-slate-700' => $product->stock > 5,
                                        ])>{{ $product->stock }}</span>
                                    </td>
                                    <td>
                                        @if ($product->is_active)
                                            <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-200">Live</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600 ring-1 ring-inset ring-slate-200">Hidden</span>
                                        @endif
                                    </td>
                                    <td>
                                        <x-stars :rating="$product->reviews_avg_rating ?? 0" :count="$product->reviews_count" />
                                    </td>
                                    <td>
                                        <div class="flex items-center justify-end gap-3">
                                            <a href="{{ route('products.show', $product) }}" class="text-sm font-medium text-slate-500 hover:text-slate-900">View</a>
                                            <a href="{{ route('farmer.products.edit', $product) }}" class="text-sm font-semibold text-emerald-700 hover:underline">Edit</a>
                                            <form id="delete-product-{{ $product->id }}" method="POST" action="{{ route('farmer.products.destroy', $product) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="text-sm font-medium text-rose-600 hover:underline"
                                                        x-on:click="$dispatch('open-modal', 'delete-product-{{ $product->id }}')">
                                                    Delete
                                                </button>
                                            </form>

                                            <x-modal name="delete-product-{{ $product->id }}" max-width="md" label="Remove this product?">
                                                <div class="p-6">
                                                    <h2 class="font-display text-lg font-semibold text-slate-900">Remove this product?</h2>
                                                    <p class="mt-2 text-sm text-slate-600">
                                                        <span class="font-medium text-slate-900">{{ $product->name }}</span> will be hidden from customers.
                                                        Existing orders are unaffected, but you can't undo this from here.
                                                    </p>
                                                    <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                                                        <button type="button" class="btn-secondary"
                                                                x-on:click="$dispatch('close-modal', 'delete-product-{{ $product->id }}')">
                                                            Keep product
                                                        </button>
                                                        <button type="submit" form="delete-product-{{ $product->id }}" class="btn-danger">
                                                            Delete product
                                                        </button>
                                                    </div>
                                                </div>
                                            </x-modal>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-8">{{ $products->links() }}</div>
        @endif
    </div>
</x-app-layout>
