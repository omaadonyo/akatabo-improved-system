<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('rental_id')->nullable()->after('quotation_id')->constrained('rentals')->nullOnDelete();
            $table->index('rental_id');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['rental_id']);
            $table->dropIndex(['rental_id']);
            $table->dropColumn('rental_id');
        });
    }
};
