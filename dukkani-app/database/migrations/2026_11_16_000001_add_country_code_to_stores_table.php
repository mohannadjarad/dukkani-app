<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            // Country dial code used to normalize phone numbers and to decide
            // which payment methods the store may offer (CliQ is Jordan only).
            $table->string('country_code', 3)->default('962')->after('currency');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('country_code');
        });
    }
};
