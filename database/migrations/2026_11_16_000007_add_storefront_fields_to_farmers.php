<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('slug', 80)->nullable()->unique()->after('name');
        });

        Schema::table('farmers', function (Blueprint $table) {
            $table->string('farm_name', 120)->nullable()->after('user_id');
            $table->string('location', 120)->nullable()->after('farm_name');
            $table->text('bio')->nullable()->after('location');
        });

        // Give existing farmers a public storefront URL.
        foreach (DB::table('users')->where('role', 'farmer')->get(['id', 'name']) as $farmer) {
            $base = Str::slug($farmer->name) ?: 'farmer';
            $slug = $base;
            $suffix = 2;

            while (DB::table('users')->where('slug', $slug)->exists()) {
                $slug = "{$base}-{$suffix}";
                $suffix++;
            }

            DB::table('users')->where('id', $farmer->id)->update(['slug' => $slug]);
        }
    }

    public function down(): void
    {
        Schema::table('farmers', function (Blueprint $table) {
            $table->dropColumn(['farm_name', 'location', 'bio']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
