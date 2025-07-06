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
        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('initiator'); // user making the request
            $table->unsignedBigInteger('moderator_account_id'); // peer/moderator receiving the request
            $table->string('request_status')->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('initiator')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('moderator_account_id')->references('id')->on('users')->onDelete('cascade');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawal_requests');
    }
};
