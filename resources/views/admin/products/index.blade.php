<x-app-layout title="Product moderation">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <x-page-header title="Product moderation" subtitle="Hide, restore or remove listings that break the rules.">
            <a href="{{ route('admin.exports.products') }}" class="btn-secondary">Export CSV</a>
        </x-page-header>

        <form method="GET" action="{{ route('admin.products.index') }}" class="mt-6 flex flex-wrap items-end gap-3">
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
                <a href="{{ route('admin.products.index') }}" class="text-sm text-slate-500 hover:text-slate-900">Reset</a>
            @endif
        </form>

        @if ($products->isEmpty())
            <div class="mt-6">
                <x-empty-state title="No products matched" description="Adjust the filters and try again." />
            </div>
        @else
            <div class="card mt-6 overflow-hidden">
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
                                            <img src="{{ $product->image_url }}" alt="" onerror="this.onerror=null;this.src='{{ asset('images/product-placeholder.svg') }}';" class="h-11 w-11 rounded-lg object-cover">
                                            <div class="min-w-0">
                                                <p class="truncate font-medium text-slate-900">{{ $product->name }}</p>
                                                <p class="text-xs text-slate-500">#{{ $product->id }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $product->farmer?->name }}</td>
                                    <td>{{ $product->category?->name }}</td>
                                    <td class="text-right"><x-money :value="$product->price" /></td>
                                    <td class="text-right">{{ $product->stock }}</td>
                                    <td>
                                        @if ($product->trashed())
                                            <span class="inline-flex rounded-full bg-rose-50 px-2.5 py-0.5 text-xs font-semibold text-rose-700 ring-1 ring-inset ring-rose-200">Removed</span>
                                        @elseif ($product->is_active)
                                            <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-200">Live</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600 ring-1 ring-inset ring-slate-200">Hidden</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="flex items-center justify-end gap-3">
                                            @if ($product->trashed())
                                                <form method="POST" action="{{ route('admin.products.restore', $product->id) }}">
                                                    @csrf
                                                    <button type="submit" class="text-sm font-semibold text-emerald-700 hover:underline">Restore</button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('admin.products.toggle', $product) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-sm font-medium text-slate-600 hover:underline">
                                                        {{ $product->is_active ? 'Hide' : 'Publish' }}
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.products.destroy', $product) }}"
                                                      onsubmit="return confirm('Remove this listing? Order history is preserved.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-sm font-medium text-rose-600 hover:underline">Remove</button>
                                                </form>
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
