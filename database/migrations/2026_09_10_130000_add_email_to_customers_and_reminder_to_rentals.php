<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('customers') && !Schema::hasColumn('customers', 'email')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->string('email')->nullable()->after('phone');
            });
        }

        if (Schema::hasTable('rentals') && !Schema::hasColumn('rentals', 'pickup_reminder_sent_at')) {
            Schema::table('rentals', function (Blueprint $table) {
                $table->timestamp('pickup_reminder_sent_at')->nullable()->after('scheduled_return_time');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('customers') && Schema::hasColumn('customers', 'email')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropColumn('email');
            });
        }

        if (Schema::hasTable('rentals') && Schema::hasColumn('rentals', 'pickup_reminder_sent_at')) {
            Schema::table('rentals', function (Blueprint $table) {
                $table->dropColumn('pickup_reminder_sent_at');
            });
        }
    }
};
