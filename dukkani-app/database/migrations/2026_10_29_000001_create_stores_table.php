<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('cover_path')->nullable();
            $table->string('phone', 30);
            $table->string('whatsapp', 30)->nullable();
            $table->string('instagram', 100)->nullable();
            $table->string('city', 100)->default('عمّان');
            $table->string('address')->nullable();
            $table->string('currency', 3)->default('JOD');
            $table->unsignedBigInteger('delivery_fee_fils')->default(0);
            $table->unsignedTinyInteger('deposit_percent')->default(0);
            $table->boolean('pickup_enabled')->default(true);
            $table->boolean('delivery_enabled')->default(true);
            $table->unsignedSmallInteger('lead_time_hours')->default(48);
            $table->text('deposit_instructions')->nullable();
            $table->string('timezone')->default('Asia/Amman');
            $table->string('status')->default('active');
            $table->timestamp('onboarding_completed_at')->nullable();
            $table->timestamps();

            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
