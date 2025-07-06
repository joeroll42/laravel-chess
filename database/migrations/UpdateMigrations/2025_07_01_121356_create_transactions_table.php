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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('request_type'); // e.g., 'withdrawal'
            $table->unsignedBigInteger('request_id'); // links to withdrawal_requests.id
            $table->unsignedBigInteger('transaction_origin'); // initiator
            $table->unsignedBigInteger('transaction_destination'); // moderator
            $table->decimal('amount', 10, 2);
            $table->string('currency')->default('KES');
            $table->boolean('delivery_confirmation_status')->default(false);
            $table->string('transaction_stage')->default('initiated');
            $table->boolean('confirmation_status')->default(false);
            $table->boolean('transaction_complete_status')->default(false);
            $table->json('transaction_notes')->nullable();
            $table->timestamps();

            // Foreign keys (optional for strict integrity)
            $table->foreign('transaction_origin')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('transaction_destination')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
