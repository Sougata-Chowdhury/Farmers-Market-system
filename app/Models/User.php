<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'role',
        'address',
        'password',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (User $user): void {
            if ($user->isFarmer() && blank($user->slug)) {
                $user->slug = static::uniqueSlug($user->name);
            }
        });
    }

    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'farmer';
        $slug = $base;
        $suffix = 2;

        while (
            static::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    public function getInitialsAttribute(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $part): string => Str::upper(Str::substr($part, 0, 1)))
            ->implode('');
    }

    /**
     * The name shown on a farmer's public storefront.
     */
    public function getStorefrontNameAttribute(): string
    {
        return $this->farmerProfile?->farm_name ?: $this->name;
    }

    public function getStorefrontUrlAttribute(): ?string
    {
        return $this->isFarmer() && filled($this->slug)
            ? route('farmers.show', $this)
            : null;
    }

    /**
     * Average rating across every review left on this farmer's products.
     */
    public function farmerRating(): float
    {
        return round((float) Review::query()
            ->whereHas('product', fn ($query) => $query->where('farmer_id', $this->id))
            ->avg('rating'), 1);
    }

    public function farmerReviewsCount(): int
    {
        return Review::query()
            ->whereHas('product', fn ($query) => $query->where('farmer_id', $this->id))
            ->count();
    }

    public function farmerProfile(): HasOne
    {
        return $this->hasOne(Farmer::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'farmer_id');
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function defaultAddress(): ?Address
    {
        return $this->addresses()->orderByDesc('is_default')->orderBy('id')->first();
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'farmer_id');
    }

    public function isCustomer(): bool
    {
        return $this->role === UserRole::Customer;
    }

    public function isFarmer(): bool
    {
        return $this->role === UserRole::Farmer;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function cartCount(): int
    {
        return (int) $this->carts()->sum('quantity');
    }
}
