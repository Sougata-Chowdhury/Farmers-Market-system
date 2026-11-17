<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('label', 40)->default('Home');
            $table->string('recipient_name', 120);
            $table->string('phone', 20);
            $table->text('address_line');
            $table->string('area', 120)->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'is_default']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_method', 20)->default('cod')->after('status');
            $table->foreignId('address_id')->nullable()->after('payment_method')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('address_id');
            $table->dropColumn('payment_method');
        });

        Schema::dropIfExists('addresses');
    }
};
