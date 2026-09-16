<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->index(['start_date', 'end_date', 'status'], 'idx_rentals_dates_status');
            $table->index(['source', 'status'], 'idx_rentals_source_status');
            $table->index('status', 'idx_rentals_status');
        });

        Schema::table('rental_details', function (Blueprint $table) {
            $table->index(['item_unit_id', 'rental_id'], 'idx_rental_details_unit_rental');
        });

        Schema::table('item_units', function (Blueprint $table) {
            $table->index(['item_id', 'status'], 'idx_item_units_item_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->dropIndex('idx_rentals_dates_status');
            $table->dropIndex('idx_rentals_source_status');
            $table->dropIndex('idx_rentals_status');
        });

        Schema::table('rental_details', function (Blueprint $table) {
            $table->dropIndex('idx_rental_details_unit_rental');
        });

        Schema::table('item_units', function (Blueprint $table) {
            $table->dropIndex('idx_item_units_item_status');
        });
    }
};
