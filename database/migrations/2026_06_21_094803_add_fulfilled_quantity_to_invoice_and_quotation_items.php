<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->decimal('fulfilled_quantity', 10, 2)->nullable()->after('quantity');
        });

        Schema::table('quotation_items', function (Blueprint $table) {
            $table->decimal('fulfilled_quantity', 10, 2)->nullable()->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn('fulfilled_quantity');
        });

        Schema::table('quotation_items', function (Blueprint $table) {
            $table->dropColumn('fulfilled_quantity');
        });
    }
};
