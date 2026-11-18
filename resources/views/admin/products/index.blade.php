<x-app-layout title="Product moderation">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <p class="eyebrow mb-2">The market shelves</p>
        <x-page-header title="Product moderation" subtitle="Hide, restore or remove listings that break the rules.">
            <a href="{{ route('admin.exports.products') }}" class="btn-secondary" data-csv-export data-filename="products.csv" download>Export CSV</a>
        </x-page-header>

        <form method="GET" action="{{ route('admin.products.index') }}" class="card mt-6 flex flex-wrap items-end gap-3 p-4">
            <p class="eyebrow w-full">Filter the shelves</p>

            <div>
                <label for="q" class="label">Search</label>
                <input id="q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Product name" class="input mt-1 w-56">
            </div>

            <div>
                <label for="category" class="label">Category</label>
                <select id="category" name="category" class="input mt-1 w-44">
                    <option value="">All categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->slug }}" @selected(($filters['category'] ?? '') === $category->slug)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="state" class="label">State</label>
                <select id="state" name="state" class="input mt-1 w-40">
                    <option value="">All</option>
                    @foreach (['active' => 'Live', 'inactive' => 'Hidden', 'removed' => 'Removed'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['state'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn-primary">Apply</button>
            @if (array_filter($filters ?? []))
                <a href="{{ route('admin.products.index') }}" class="inline-flex items-center px-1 py-2.5 font-mono text-[10px] font-bold uppercase tracking-label text-ink-faint transition hover:text-clay-600">Reset</a>
            @endif
        </form>

        @if ($products->isEmpty())
            <div class="mt-6">
                <x-empty-state title="No products matched" description="Adjust the filters and try again." />
            </div>
        @else
            <div class="card mt-6 overflow-hidden">
                <div class="card-head">
                    <div>
                        <p class="eyebrow">Everything listed</p>
                        <h2 class="mt-1 font-display text-xl font-semibold text-ink">Listing ledger</h2>
                    </div>
                    <span class="tag">{{ number_format($products->total()) }} {{ \Illuminate\Support\Str::plural('listing', $products->total()) }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="table-base">
                        <thead>
                            <tr>
                                <th scope="col">Product</th>
                                <th scope="col">Farmer</th>
                                <th scope="col">Category</th>
                                <th scope="col" class="text-right">Price</th>
                                <th scope="col" class="text-right">Stock</th>
                                <th scope="col">State</th>
                                <th scope="col" class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $product)
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <img src="{{ $product->image_url }}" alt="" onerror="this.onerror=null;this.src='{{ asset('images/product-placeholder.svg') }}';" class="h-11 w-11 shrink-0 rounded-sm border border-line bg-paper-light object-cover">
                                            <div class="min-w-0">
                                                <p class="truncate font-medium text-ink">{{ $product->name }}</p>
                                                <p class="font-mono text-[10px] uppercase tracking-label text-ink-faint">#{{ $product->id }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-ink-soft">{{ $product->farmer?->name }}</td>
                                    <td class="text-ink-soft">{{ $product->category?->name }}</td>
                                    <td class="text-right"><x-money :value="$product->price" /></td>
                                    <td class="text-right font-mono text-[13px] text-ink-soft">{{ $product->stock }}</td>
                                    <td>
                                        @if ($product->trashed())
                                            <span class="inline-flex items-center gap-1.5 rounded-sm border border-clay-300 bg-clay-50 px-2 py-0.5 font-mono text-[9px] font-bold uppercase tracking-label text-clay-700"><span class="inline-block h-1.5 w-1.5 rounded-full bg-current" aria-hidden="true"></span>Removed</span>
                                        @elseif ($product->is_active)
                                            <span class="inline-flex items-center gap-1.5 rounded-sm border border-forest-300 bg-forest-50 px-2 py-0.5 font-mono text-[9px] font-bold uppercase tracking-label text-forest-800"><span class="inline-block h-1.5 w-1.5 rounded-full bg-current" aria-hidden="true"></span>Live</span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded-sm border border-line-dark bg-paper-deep px-2 py-0.5 font-mono text-[9px] font-bold uppercase tracking-label text-ink-soft"><span class="inline-block h-1.5 w-1.5 rounded-full bg-current" aria-hidden="true"></span>Hidden</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="flex items-center justify-end gap-3">
                                            @if ($product->trashed())
                                                <form method="POST" action="{{ route('admin.products.restore', $product->id) }}">
                                                    @csrf
                                                    <button type="submit" class="font-mono text-[10px] font-bold uppercase tracking-label text-forest-700 hover:underline">Restore</button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('admin.products.toggle', $product) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="font-mono text-[10px] font-bold uppercase tracking-label text-ink-soft hover:underline">
                                                        {{ $product->is_active ? 'Hide' : 'Publish' }}
                                                    </button>
                                                </form>
                                                <form id="remove-product-{{ $product->id }}" method="POST" action="{{ route('admin.products.destroy', $product) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="font-mono text-[10px] font-bold uppercase tracking-label text-clay-600 hover:underline"
                                                            x-on:click="$dispatch('open-modal', 'remove-product-{{ $product->id }}')">
                                                        Remove
                                                    </button>
                                                </form>

                                                <x-modal name="remove-product-{{ $product->id }}" max-width="md" label="Remove this listing?">
                                                    <div class="p-6">
                                                        <p class="eyebrow">Confirm removal</p>
                                                        <h2 class="mt-1 font-display text-lg font-semibold text-ink">Remove this listing?</h2>
                                                        <p class="mt-2 text-sm text-ink-soft">
                                                            <span class="font-medium text-ink">{{ $product->name }}</span> will disappear from the marketplace.
                                                            Past orders keep their records.
                                                        </p>
                                                        <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                                                            <button type="button" class="btn-secondary"
                                                                    x-on:click="$dispatch('close-modal', 'remove-product-{{ $product->id }}')">
                                                                Keep listing
                                                            </button>
                                                            <button type="submit" form="remove-product-{{ $product->id }}" class="btn-danger">
                                                                Remove listing
                                                            </button>
                                                        </div>
                                                    </div>
                                                </x-modal>
                                            @endif
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
