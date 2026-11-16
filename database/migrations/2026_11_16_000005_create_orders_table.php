<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('status', 20)->default('pending')->index();
            $table->string('delivery_name', 120);
            $table->string('delivery_phone', 20);
            $table->text('delivery_address');
            $table->text('notes')->nullable();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('platform_fee', 10, 2);
            $table->decimal('total', 10, 2);
            $table->timestamp('placed_at');
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('farmer_id')->constrained('users')->restrictOnDelete();
            $table->string('product_name', 150);
            $table->decimal('unit_price', 10, 2);
            $table->unsignedInteger('quantity');
            $table->decimal('line_total', 10, 2);
            $table->timestamps();

            $table->index(['farmer_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
