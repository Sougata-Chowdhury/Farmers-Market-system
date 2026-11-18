<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductPlaceholder
{
    /**
     * Ink colour per category, drawn from the market stall palette.
     *
     * @var array<string, string>
     */
    private const PALETTE = [
        'Vegetables' => '#3B5232',
        'Fruits' => '#A9542F',
        'Fish' => '#2F4C52',
        'Meat' => '#7D3728',
        'Rice' => '#8A6A26',
        'Dairy' => '#43566B',
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
        $ink = self::PALETTE[$category] ?? '#665845';

        $lines = self::wrap($name, 18);
        $textTop = 448;
        $text = '';
        foreach ($lines as $index => $line) {
            $y = $textTop + $index * 42;
            $text .= '<text x="300" y="'.$y.'" text-anchor="middle" font-family="Bitter, Georgia, \'Times New Roman\', serif" font-size="31" font-weight="700" fill="#2A231B">'
                .self::escape($line).'</text>';
        }

        $label = self::escape(Str::upper($category));
        $motif = self::motif($category);

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="600" height="600" viewBox="0 0 600 600" role="img" aria-label="{$label}">
    <defs>
        <pattern id="grain" width="26" height="26" patternUnits="userSpaceOnUse">
            <circle cx="4" cy="4" r="1" fill="#2A231B" opacity="0.06"/>
            <circle cx="17" cy="13" r="0.8" fill="#2A231B" opacity="0.05"/>
            <circle cx="9" cy="21" r="0.8" fill="#2A231B" opacity="0.04"/>
        </pattern>
    </defs>
    <rect width="600" height="600" fill="#F7F0E1"/>
    <rect width="600" height="600" fill="url(#grain)"/>
    <rect x="18" y="18" width="564" height="564" fill="none" stroke="{$ink}" stroke-width="2" opacity="0.45"/>
    <rect x="27" y="27" width="546" height="546" fill="none" stroke="{$ink}" stroke-width="1" stroke-dasharray="7 7" opacity="0.32"/>
    <g transform="translate(300 240)" fill="none" stroke="{$ink}" stroke-width="7" stroke-linecap="round" stroke-linejoin="round" opacity="0.62">
        {$motif}
    </g>
    <text x="300" y="402" text-anchor="middle" font-family="Karla, 'Segoe UI', sans-serif" font-size="14" font-weight="700" letter-spacing="5" fill="{$ink}" opacity="0.85">{$label}</text>
    {$text}
</svg>
SVG;
    }

    /**
     * A simple stamped illustration for each aisle.
     */
    private static function motif(string $category): string
    {
        return match ($category) {
            'Vegetables' => <<<'SVG'
<path d="M0 78C-34 44-40-8-6-56"/>
<path d="M-6-56C34-8 30 44 0 78"/>
<path d="M-3 66C-3 24-4-16-6-52"/>
<path d="M-49 12C-64-8-64-32-48-48C-31-37-29-13-41 4Z"/>
<path d="M45 20C60 0 61-24 46-40C29-29 27-5 39 12Z"/>
<path d="M-20-20C-26-44-18-64-2-74C10-57 8-34-6-22Z"/>
SVG,
            'Fruits' => <<<'SVG'
<path d="M0-34C-58-36-78 8-62 42C-46 76-12 82 0 62C12 82 46 76 62 42C78 8 58-36 0-34Z"/>
<path d="M0-34C-2-56 6-68 22-76"/>
<path d="M18-62C36-84 62-80 70-62C52-48 30-48 18-62Z"/>
SVG,
            'Fish' => <<<'SVG'
<path d="M-84 0C-52-46 24-52 58 0C24 52-52 46-84 0Z"/>
<path d="M58 0L96-30V30Z"/>
<path d="M-30-14C-14-2-14 2-30 14"/>
<circle cx="16" cy="-10" r="5" fill="#2F4C52" stroke="none"/>
SVG,
            'Meat' => <<<'SVG'
<path d="M-50 4C-50-26-28-46 0-46C28-46 40-26 40-10C40 12 22 34 0 34C-24 34-50 28-50 4Z"/>
<circle cx="44" cy="-32" r="16"/>
<path d="M60-34L78-28L60-22"/>
<path d="M34-46C34-58 44-64 52-58"/>
<path d="M46-16C46-6 54-4 57-11"/>
<path d="M-46-16C-66-26-76-46-70-64C-54-56-44-40-46-16Z"/>
<path d="M-8 34L-11 54M-11 54L-22 62M-11 54L-1 62"/>
<path d="M12 32L15 52M15 52L4 60M15 52L26 60"/>
SVG,
            'Rice' => <<<'SVG'
<path d="M0 84V-42"/>
<path d="M0 8C-22-4-30-24-26-48"/>
<path d="M0 34C22 22 30 2 26-22"/>
<path d="M-26-48C-46-56-52-76-42-92C-22-86-14-66-26-48Z"/>
<path d="M26-22C46-30 54-50 44-66C24-60 16-40 26-22Z"/>
<path d="M0-42C-18-54-20-76-6-90C12-80 16-58 0-42Z"/>
<path d="M-4 60C-22 52-26 34-16 20"/>
<path d="M4 76C22 68 26 50 16 36"/>
SVG,
            'Dairy' => <<<'SVG'
<path d="M-34 76V16C-52 4-52-18-34-30V-52"/>
<path d="M34 76V16C52 4 52-18 34-30V-52"/>
<path d="M-34 76H34"/>
<path d="M-44-52H44V-68C44-76 38-82 30-82H-30C-38-82-44-76-44-68Z"/>
<path d="M-18-46H18"/>
SVG,
            default => <<<'SVG'
<path d="M0 76C-40 44-44-14 0-76C44-14 40 44 0 76Z"/>
<path d="M0 62V-56"/>
SVG,
        };
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
