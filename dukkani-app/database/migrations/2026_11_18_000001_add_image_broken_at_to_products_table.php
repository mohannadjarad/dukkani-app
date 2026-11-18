<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Set when a visitor's browser fails to load the stored image, or
            // when products:check-images finds the link dead. Cleared as soon
            // as the owner saves a working image, so the storefront can skip
            // the dead request entirely and the dashboard can warn her.
            $table->timestamp('image_broken_at')->nullable()->after('image_public_id');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('image_broken_at');
        });
    }
};