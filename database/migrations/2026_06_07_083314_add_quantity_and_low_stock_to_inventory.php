<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products_services', function (Blueprint $table) {
            $table->decimal('quantity', 12, 2)->default(0)->after('selling_price');
            $table->decimal('low_stock_threshold', 12, 2)->default(10)->after('quantity');
        });

        Schema::table('fabrics', function (Blueprint $table) {
            $table->decimal('low_stock_threshold', 10, 2)->default(10)->after('used_meters');
        });
    }

    public function down(): void
    {
        Schema::table('products_services', function (Blueprint $table) {
            $table->dropColumn(['quantity', 'low_stock_threshold']);
        });

        Schema::table('fabrics', function (Blueprint $table) {
            $table->dropColumn('low_stock_threshold');
        });
    }
};
