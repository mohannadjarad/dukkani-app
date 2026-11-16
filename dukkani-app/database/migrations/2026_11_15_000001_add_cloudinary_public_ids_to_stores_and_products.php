<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->string('logo_public_id')->nullable();
            $table->string('cover_public_id')->nullable();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('image_public_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('image_public_id');
        });

        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['logo_public_id', 'cover_public_id']);
        });
    }
};
