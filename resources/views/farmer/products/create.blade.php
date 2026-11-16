<x-app-layout title="Add product">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <x-page-header title="Add a product" subtitle="List something new from your farm.">
            <a href="{{ route('farmer.products.index') }}" class="btn-secondary">Back to products</a>
        </x-page-header>

        @include('farmer.products._form', ['product' => $product, 'categories' => $categories])
    </div>
</x-app-layout>
