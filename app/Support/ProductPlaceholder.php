<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductPlaceholder
{
    /**
     * Category colour pairs used for the generated thumbnails.
     *
     * @var array<string, array{0: string, 1: string}>
     */
    private const PALETTE = [
        'Vegetables' => ['#065f46', '#10b981'],
        'Fruits' => ['#9a3412', '#f59e0b'],
        'Fish' => ['#0c4a6e', '#0ea5e9'],
        'Meat' => ['#7f1d1d', '#ef4444'],
        'Rice' => ['#713f12', '#eab308'],
        'Dairy' => ['#3730a3', '#818cf8'],
    ];

    /**
     * Generate (once) a local SVG thumbnail for a product and return its path
     * on the public disk.
     */
    public static function ensure(string $name, string $category): string
    {
        $slug = Str::slug($name) ?: 'product';
        $path = "products/{$slug}-placeholder.svg";

        if (! Storage::disk('public')->exists($path)) {
            Storage::disk('public')->put($path, self::svg($name, $category));
        }

        return $path;
    }

    public static function svg(string $name, string $category): string
    {
        [$from, $to] = self::PALETTE[$category] ?? ['#334155', '#64748b'];

        $lines = self::wrap($name, 20);
        $startY = 330 - (count($lines) - 1) * 23;

        $text = '';
        foreach ($lines as $index => $line) {
            $y = $startY + $index * 46;
            $text .= '<text x="300" y="'.$y.'" text-anchor="middle" font-family="Figtree, Segoe UI, sans-serif" font-size="34" font-weight="600" fill="#ffffff">'
                .self::escape($line).'</text>';
        }

        $label = self::escape(Str::upper($category));

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="600" height="600" viewBox="0 0 600 600" role="img" aria-label="{$label}">
    <defs>
        <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0%" stop-color="{$from}"/>
            <stop offset="100%" stop-color="{$to}"/>
        </linearGradient>
    </defs>
    <rect width="600" height="600" fill="url(#bg)"/>
    <circle cx="520" cy="60" r="160" fill="#ffffff" opacity="0.06"/>
    <circle cx="60" cy="540" r="150" fill="#ffffff" opacity="0.05"/>
    <g transform="translate(488 470) scale(2.2)" fill="#ffffff" opacity="0.18">
        <path d="M12 2C8.5 4.5 5 8 5 12.5A7 7 0 0 0 12 19.5a7 7 0 0 0 7-7C19 8 15.5 4.5 12 2Z"/>
        <path d="M12 21.5c-.4-4.2.6-7.4 3.2-10.1a.75.75 0 1 1 1.1 1c-2.3 2.5-3 5.2-2.7 9a.75.75 0 0 1-1.5.1Z" fill="#0f172a" opacity="0.25"/>
    </g>
    <text x="300" y="140" text-anchor="middle" font-family="Figtree, Segoe UI, sans-serif" font-size="15" font-weight="700" letter-spacing="4" fill="#ffffff" opacity="0.75">{$label}</text>
    {$text}
</svg>
SVG;
    }

    /**
     * @return array<int, string>
     */
    private static function wrap(string $text, int $limit): array
    {
        $words = preg_split('/\s+/', trim($text)) ?: [];
        $lines = [];
        $line = '';

        foreach ($words as $word) {
            $candidate = $line === '' ? $word : "{$line} {$word}";

            if (Str::length($candidate) > $limit && $line !== '') {
                $lines[] = $line;
                $line = $word;

                if (count($lines) === 3) {
                    break;
                }

                continue;
            }

            $line = $candidate;
        }

        if ($line !== '' && count($lines) < 3) {
            $lines[] = $line;
        }

        return $lines === [] ? ['Fresh produce'] : $lines;
    }

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
