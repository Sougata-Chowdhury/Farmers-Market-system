<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $products = Product::query()
            ->where('is_active', true)
            ->orderByDesc('updated_at')
            ->get(['id', 'name', 'slug', 'updated_at']);

        $farmers = User::query()
            ->whereNotNull('slug')
            ->whereHas('products', fn ($query) => $query->where('is_active', true))
            ->orderByDesc('updated_at')
            ->get(['id', 'name', 'slug', 'updated_at']);

        return response()
            ->view('sitemap', [
                'products' => $products,
                'farmers' => $farmers,
            ])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * Robots rules are served dynamically so the sitemap URL always matches
     * the current host.
     */
    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /farmer',
            'Disallow: /cart',
            'Disallow: /checkout',
            'Disallow: /orders',
            'Disallow: /profile',
            'Disallow: /addresses',
            'Disallow: /notifications',
            'Disallow: /wishlist',
            'Disallow: /dashboard',
            'Disallow: /login',
            'Disallow: /register',
            '',
            'Sitemap: '.url('/sitemap.xml'),
            '',
        ];

        return response(implode("\n", $lines))
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
