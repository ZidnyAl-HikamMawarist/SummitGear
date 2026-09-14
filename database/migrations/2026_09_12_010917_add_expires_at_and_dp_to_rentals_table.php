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
            if (!Schema::hasColumn('rentals', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('scheduled_return_time');
            }
            if (!Schema::hasColumn('rentals', 'down_payment_amount')) {
                $table->bigInteger('down_payment_amount')->default(0)->after('deposit_amount');
            }
            if (!Schema::hasColumn('rentals', 'payment_type')) {
                $table->string('payment_type')->nullable()->after('down_payment_amount');
            }

            $table->index(['status', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->dropIndex(['status', 'expires_at']);
            if (Schema::hasColumn('rentals', 'expires_at')) {
                $table->dropColumn('expires_at');
            }
            if (Schema::hasColumn('rentals', 'down_payment_amount')) {
                $table->dropColumn('down_payment_amount');
            }
            if (Schema::hasColumn('rentals', 'payment_type')) {
                $table->dropColumn('payment_type');
            }
        });
    }
};
