@php($editing = $product->exists)

<form method="POST"
      action="{{ $editing ? route('farmer.products.update', $product) : route('farmer.products.store') }}"
      enctype="multipart/form-data"
      class="mt-6 grid gap-6 lg:grid-cols-[1.4fr_0.6fr] lg:items-start">
    @csrf
    @if ($editing)
        @method('PATCH')
    @endif

    <div class="space-y-6">
        <fieldset class="card overflow-hidden">
            <legend class="sr-only">Product details</legend>
            <div class="card-head">
                <div>
                    <p class="eyebrow">On the label</p>
                    <h2 class="font-display text-lg font-semibold text-ink">Product details</h2>
                </div>
            </div>

            <div class="space-y-5 p-5 sm:p-6">
                <p class="text-sm leading-relaxed text-ink-soft">Buyers see this information on the product page.</p>

                <div>
                    <label for="name" class="label">Product name</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $product->name) }}" required
                           class="input mt-1" placeholder="e.g. Fresh Red Tomato">
                    @error('name') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="category_id" class="label">Category</label>
                    <select id="category_id" name="category_id" required class="input mt-1">
                        <option value="">Select a category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((int) old('category_id', $product->category_id) === $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="description" class="label">Description <span class="text-ink-faint">(optional)</span></label>
                    <textarea id="description" name="description" rows="4" class="input mt-1"
                              placeholder="How it was grown, what makes it special, packaging details...">{{ old('description', $product->description) }}</textarea>
                    @error('description') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            </div>
        </fieldset>

        <fieldset class="card overflow-hidden">
            <legend class="sr-only">Pricing and stock</legend>
            <div class="card-head">
                <div>
                    <p class="eyebrow">At the counter</p>
                    <h2 class="font-display text-lg font-semibold text-ink">Price &amp; stock</h2>
                </div>
            </div>

            <div class="p-5 sm:p-6">
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label for="price" class="label">Price ({{ config('marketplace.currency.symbol') }})</label>
                        <input id="price" name="price" type="number" step="0.01" min="0.01" value="{{ old('price', $product->price) }}" required
                               class="input mt-1" placeholder="0.00">
                        @error('price') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="stock" class="label">Stock</label>
                        <input id="stock" name="stock" type="number" min="0" value="{{ old('stock', $product->stock) }}" required
                               class="input mt-1" placeholder="0">
                        @error('stock') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="unit" class="label">Sold by</label>
                        <select id="unit" name="unit" required class="input mt-1">
                            @foreach (['kg' => 'Kilogram (kg)', 'piece' => 'Piece', 'bundle' => 'Bundle', 'dozen' => 'Dozen', 'litre' => 'Litre'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('unit', $product->unit ?? 'kg') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('unit') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </fieldset>
    </div>

    <aside class="space-y-4">
        <div class="card overflow-hidden">
            <div class="card-head">
                <div>
                    <p class="eyebrow">Gallery</p>
                    <h2 class="font-display text-lg font-semibold text-ink">Photos</h2>
                </div>
            </div>

            <div class="p-5">
                <p class="text-sm leading-relaxed text-ink-soft">The first photo becomes the cover on the marketplace.</p>

                @if ($editing && ($media = $product->getMedia('gallery'))->isNotEmpty())
                    <div class="mt-3 grid grid-cols-3 gap-2">
                        @foreach ($media as $photo)
                            <div class="group relative overflow-hidden border border-line bg-paper-light">
                                <img src="{{ $photo->hasGeneratedConversion('thumb') ? $photo->getUrl('thumb') : $photo->getUrl() }}"
                                     alt="" class="aspect-square w-full object-cover">
                                <button type="submit" form="delete-media-{{ $photo->id }}"
                                        class="absolute right-1 top-1 grid h-6 w-6 place-items-center border border-ink bg-ink/80 text-white opacity-0 transition group-hover:opacity-100 focus:opacity-100"
                                        aria-label="Remove photo" title="Remove photo">
                                    &times;
                                </button>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="mt-3 overflow-hidden border border-line bg-paper-light">
                        <img src="{{ $product->image_url }}" alt="" onerror="this.onerror=null;this.src='{{ asset('images/product-placeholder.svg') }}';" class="aspect-[4/3] w-full object-cover">
                    </div>
                @endif

                <label for="images" class="label mt-4">Add photos</label>
                <input id="images" name="images[]" type="file" accept="image/jpeg,image/png,image/webp" multiple
                       class="mt-1 block w-full text-sm text-ink-soft file:mr-3 file:rounded-sm file:border file:border-line-dark file:bg-paper-deep file:px-3 file:py-2 file:font-mono file:text-[10px] file:font-bold file:uppercase file:tracking-label file:text-ink-soft hover:file:bg-paper-light">
                <p class="mt-2 text-xs leading-relaxed text-ink-soft">JPG, PNG or WebP up to 4 MB each. Upload up to 5 at a time; they are converted to WebP automatically.</p>
                @error('images') <p class="field-error">{{ $message }}</p> @enderror
                @error('images.*') <p class="field-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="card space-y-3 p-5">
            <p class="eyebrow">Publish</p>
            <p class="text-sm leading-relaxed text-ink-soft">
                Published products appear instantly for customers. You can hide or edit them at any time.
            </p>
            <button type="submit" class="btn-primary w-full">{{ $editing ? 'Save changes' : 'Publish product' }}</button>
            <a href="{{ route('farmer.products.index') }}" class="btn-secondary w-full">Cancel</a>
        </div>
    </aside>
</form>

@if ($editing)
    @foreach ($product->getMedia('gallery') as $photo)
        <form id="delete-media-{{ $photo->id }}" method="POST"
              action="{{ route('farmer.products.media.destroy', [$product, $photo]) }}" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endforeach
@endif
