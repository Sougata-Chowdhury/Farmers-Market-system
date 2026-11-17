<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Product;
use App\Support\CsvExport;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function orders(Request $request): StreamedResponse
    {
        $farmerId = $request->user()->id;

        $items = OrderItem::query()
            ->where('farmer_id', $farmerId)
            ->with(['order:id,placed_at,status,user_id', 'order.user:id,name'])
            ->orderByDesc('id')
            ->lazy(200);

        $rows = $items->map(fn (OrderItem $item): array => [
            $item->order_id,
            $item->order?->placed_at?->toDateTimeString(),
            $item->order?->user?->name,
            $item->order?->status->label(),
            $item->product_name,
            $item->quantity,
            $item->unit_price,
            $item->line_total,
        ]);

        return CsvExport::stream(
            'my-sales-'.now()->format('Y-m-d').'.csv',
            ['Order', 'Placed at', 'Customer', 'Status', 'Product', 'Qty', 'Unit price', 'Line total'],
            $rows
        );
    }

    public function products(Request $request): StreamedResponse
    {
        $products = Product::query()
            ->where('farmer_id', $request->user()->id)
            ->with('category:id,name')
            ->withCount('orderItems as sold')
            ->withSum('orderItems as revenue', 'line_total')
            ->orderBy('id')
            ->lazy(200);

        $rows = $products->map(fn (Product $product): array => [
            $product->id,
            $product->name,
            $product->category?->name,
            $product->price,
            $product->stock,
            $product->unit,
            $product->is_active ? 'Active' : 'Hidden',
            (int) $product->sold,
            round((float) $product->revenue, 2),
        ]);

        return CsvExport::stream(
            'my-products-'.now()->format('Y-m-d').'.csv',
            ['ID', 'Product', 'Category', 'Price', 'Stock', 'Unit', 'Visibility', 'Units sold', 'Revenue'],
            $rows
        );
    }
}
