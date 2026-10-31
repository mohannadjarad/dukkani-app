<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('number', 30);
            $table->uuid('public_token')->unique();
            $table->string('status')->default('new');
            $table->string('fulfillment_type')->default('delivery');
            $table->dateTime('scheduled_at');
            $table->text('delivery_address')->nullable();
            $table->unsignedBigInteger('subtotal_fils');
            $table->unsignedBigInteger('delivery_fee_fils')->default(0);
            $table->unsignedBigInteger('discount_fils')->default(0);
            $table->unsignedBigInteger('total_fils');
            $table->unsignedBigInteger('deposit_fils')->default(0);
            $table->string('deposit_status')->default('none');
            $table->string('payment_method')->nullable();
            $table->unsignedBigInteger('balance_due_fils')->default(0);
            $table->text('customer_notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->string('source')->default('storefront');
            $table->string('reference_image_path')->nullable();
            $table->timestamp('consent_at')->nullable();
            $table->timestamps();

            $table->unique(['store_id', 'number']);
            $table->index(['store_id', 'status', 'scheduled_at']);
            $table->index(['store_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
