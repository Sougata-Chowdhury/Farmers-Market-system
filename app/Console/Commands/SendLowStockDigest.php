<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Mail\LowStockDigest;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendLowStockDigest extends Command
{
    protected $signature = 'marketplace:low-stock-digest {--threshold= : Override the configured stock threshold}';

    protected $description = 'Email farmers a digest of products that are low on stock';

    public function handle(): int
    {
        $threshold = (int) ($this->option('threshold') ?? config('marketplace.low_stock_threshold'));
        $sent = 0;

        User::query()
            ->where('role', UserRole::Farmer)
            ->whereHas('products', fn ($query) => $query->where('is_active', true)->where('stock', '<=', $threshold))
            ->with(['products' => fn ($query) => $query
                ->where('is_active', true)
                ->where('stock', '<=', $threshold)
                ->orderBy('stock')])
            ->chunkById(100, function ($farmers) use (&$sent): void {
                foreach ($farmers as $farmer) {
                    Mail::to($farmer)->queue(new LowStockDigest($farmer, $farmer->products));
                    $farmer->notify(new LowStockNotification($farmer->products));

                    $this->line("Queued low-stock digest for {$farmer->name} ({$farmer->products->count()} products).");
                    $sent++;
                }
            });

        $this->info("Low-stock digest queued for {$sent} farmer(s) at threshold {$threshold}.");

        return self::SUCCESS;
    }
}
