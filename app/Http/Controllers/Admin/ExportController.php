<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Support\CsvExport;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function orders(): StreamedResponse
    {
        $orders = Order::query()
            ->with('user:id,name')
            ->orderBy('id')
            ->lazy(200);

        $rows = $orders->map(fn (Order $order): array => [
            $order->id,
            $order->placed_at?->toDateTimeString(),
            $order->user?->name,
            $order->status->label(),
            $order->payment_method->label(),
            $order->subtotal,
            $order->discount,
            $order->platform_fee,
            $order->total,
            $order->items()->count(),
        ]);

        return CsvExport::stream(
            'orders-'.now()->format('Y-m-d').'.csv',
            ['Order', 'Placed at', 'Customer', 'Status', 'Payment', 'Subtotal', 'Discount', 'Platform fee', 'Total', 'Items'],
            $rows
        );
    }

    public function products(): StreamedResponse
    {
        $products = Product::query()
            ->withTrashed()
            ->with(['farmer:id,name', 'category:id,name'])
            ->withCount('orderItems as sold')
            ->withSum('orderItems as revenue', 'line_total')
            ->orderBy('id')
            ->lazy(200);

        $rows = $products->map(fn (Product $product): array => [
            $product->id,
            $product->name,
            $product->farmer?->name,
            $product->category?->name,
            $product->price,
            $product->stock,
            $product->unit,
            $product->is_active ? 'Active' : 'Hidden',
            $product->trashed() ? 'Deleted' : 'Live',
            (int) $product->sold,
            round((float) $product->revenue, 2),
        ]);

        return CsvExport::stream(
            'products-'.now()->format('Y-m-d').'.csv',
            ['ID', 'Product', 'Farmer', 'Category', 'Price', 'Stock', 'Unit', 'Visibility', 'State', 'Units sold', 'Revenue'],
            $rows
        );
    }
}
