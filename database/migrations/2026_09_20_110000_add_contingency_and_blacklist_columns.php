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
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'is_blacklisted')) {
                $table->boolean('is_blacklisted')->default(false)->after('consent_at');
            }
            if (!Schema::hasColumn('customers', 'blacklist_notes')) {
                $table->text('blacklist_notes')->nullable()->after('is_blacklisted');
            }
        });

        Schema::table('rentals', function (Blueprint $table) {
            if (!Schema::hasColumn('rentals', 'pickup_extended_until')) {
                $table->timestamp('pickup_extended_until')->nullable()->after('expires_at');
            }
            if (!Schema::hasColumn('rentals', 'settlement_notes')) {
                $table->text('settlement_notes')->nullable()->after('source');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            if (Schema::hasColumn('rentals', 'pickup_extended_until')) {
                $table->dropColumn('pickup_extended_until');
            }
            if (Schema::hasColumn('rentals', 'settlement_notes')) {
                $table->dropColumn('settlement_notes');
            }
        });

        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'is_blacklisted')) {
                $table->dropColumn('is_blacklisted');
            }
            if (Schema::hasColumn('customers', 'blacklist_notes')) {
                $table->dropColumn('blacklist_notes');
            }
        });
    }
};
