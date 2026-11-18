<x-app-layout title="My products">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <p class="eyebrow mb-2">Harvest list</p>
        <x-page-header title="My products" subtitle="Publish, price and restock what you sell.">
            <a href="{{ route('farmer.exports.products') }}" class="btn-secondary" data-csv-export data-filename="my-products.csv" download>Export CSV</a>
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
                <div class="card-head">
                    <div>
                        <p class="eyebrow">At the stall</p>
                        <h2 class="font-display text-xl font-semibold text-ink">All produce</h2>
                    </div>
                    <p class="font-mono text-[10px] uppercase tracking-label text-ink-faint">
                        {{ $products->total() }} {{ \Illuminate\Support\Str::plural('listing', $products->total()) }}
                    </p>
                </div>

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
                                            <img src="{{ $product->image_url }}" alt="" onerror="this.onerror=null;this.src='{{ asset('images/product-placeholder.svg') }}';" class="h-12 w-12 border border-line object-cover">
                                            <div class="min-w-0">
                                                <p class="truncate font-medium text-ink">{{ $product->name }}</p>
                                                <p class="font-mono text-[10px] uppercase tracking-label text-ink-faint">Added {{ $product->created_at->format('d M Y') }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-ink-soft">{{ $product->category->name }}</td>
                                    <td class="text-right">
                                        <x-money :value="$product->price" />
                                        <span class="font-mono text-[10px] text-ink-faint">/{{ $product->unit }}</span>
                                    </td>
                                    <td class="text-right">
                                        <span @class([
                                            'font-semibold',
                                            'text-clay-600' => $product->stock <= 5,
                                            'text-ink' => $product->stock > 5,
                                        ])>{{ $product->stock }}</span>
                                    </td>
                                    <td>
                                        @if ($product->is_active)
                                            <span class="inline-flex items-center gap-1.5 rounded-sm border border-forest-300 bg-forest-50 px-2 py-0.5 font-mono text-[10px] font-bold uppercase tracking-label text-forest-800">
                                                <span class="inline-block h-1.5 w-1.5 rounded-full bg-forest-600" aria-hidden="true"></span>
                                                Live
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded-sm border border-line-dark bg-paper-deep px-2 py-0.5 font-mono text-[10px] font-bold uppercase tracking-label text-ink-soft">
                                                <span class="inline-block h-1.5 w-1.5 rounded-full bg-ink-faint" aria-hidden="true"></span>
                                                Hidden
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <x-stars :rating="$product->reviews_avg_rating ?? 0" :count="$product->reviews_count" />
                                    </td>
                                    <td>
                                        <div class="flex items-center justify-end gap-3">
                                            <a href="{{ route('products.show', $product) }}" class="font-mono text-[10px] font-bold uppercase tracking-label text-ink-soft transition hover:text-ink">View</a>
                                            <a href="{{ route('farmer.products.edit', $product) }}" class="font-mono text-[10px] font-bold uppercase tracking-label text-clay-600 hover:underline">Edit</a>
                                            <form id="delete-product-{{ $product->id }}" method="POST" action="{{ route('farmer.products.destroy', $product) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="font-mono text-[10px] font-bold uppercase tracking-label text-clay-700 hover:underline"
                                                        x-on:click="$dispatch('open-modal', 'delete-product-{{ $product->id }}')">
                                                    Delete
                                                </button>
                                            </form>

                                            <x-modal name="delete-product-{{ $product->id }}" max-width="md" label="Remove this product?">
                                                <div class="p-6">
                                                    <p class="eyebrow">Remove listing</p>
                                                    <h2 class="mt-1 font-display text-xl font-semibold text-ink">Remove this product?</h2>
                                                    <p class="mt-2 border-t border-dashed border-line-dark pt-3 text-sm leading-relaxed text-ink-soft">
                                                        <span class="font-semibold text-ink">{{ $product->name }}</span> will be hidden from customers.
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
