<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->string('plan')->default('free')->after('status');
            $table->timestamp('plan_expires_at')->nullable()->after('plan');
            $table->boolean('is_trial')->default(false)->after('plan_expires_at');
            $table->unsignedInteger('order_limit_override')->nullable()->after('is_trial');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('password');
            $table->timestamp('last_login_at')->nullable()->after('is_admin');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['plan', 'plan_expires_at', 'is_trial', 'order_limit_override']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_admin', 'last_login_at']);
        });
    }
};
