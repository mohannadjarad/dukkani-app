<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            // Set the first time a paid plan is activated, so the one-off
            // first-month bonus days are never granted twice.
            $table->timestamp('first_paid_at')->nullable()->after('order_limit_override');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('first_paid_at');
        });
    }
};
