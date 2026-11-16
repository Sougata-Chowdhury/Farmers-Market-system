<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:80'],
            'sort' => ['nullable', 'in:newest,price_asc,price_desc,rating'],
        ]);

        $sort = $filters['sort'] ?? 'newest';

        $products = Product::query()
            ->active()
            ->with(['category:id,name,slug', 'farmer:id,name'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->search($filters['q'] ?? null)
            ->when(
                $filters['category'] ?? null,
                fn ($query, $slug) => $query->whereHas('category', fn ($query) => $query->where('slug', $slug))
            );

        match ($sort) {
            'price_asc' => $products->orderBy('price'),
            'price_desc' => $products->orderByDesc('price'),
            'rating' => $products->orderByDesc('reviews_avg_rating')->orderByDesc('reviews_count'),
            default => $products->latest(),
        };

        return view('home', [
            'products' => $products->paginate(config('marketplace.products_per_page'))->withQueryString(),
            'categories' => Category::query()
                ->withCount(['products' => fn ($query) => $query->active()])
                ->orderBy('name')
                ->get(),
            'filters' => [
                'q' => $filters['q'] ?? '',
                'category' => $filters['category'] ?? '',
                'sort' => $sort,
            ],
        ]);
    }
}
