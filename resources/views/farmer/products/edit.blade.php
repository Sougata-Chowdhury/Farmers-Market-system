<x-app-layout :title="'Edit '.$product->name">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <x-page-header :title="'Edit '.$product->name" subtitle="Update pricing, stock or the product photo.">
            <a href="{{ route('products.show', $product) }}" class="btn-secondary">View listing</a>
        </x-page-header>

        @include('farmer.products._form', ['product' => $product, 'categories' => $categories])
    </div>
</x-app-layout>
