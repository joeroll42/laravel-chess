<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('challenges', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id') // challenger
            ->constrained()
                ->onDelete('cascade');

            $table->foreignId('opponent_id')
                ->nullable() // opponent
                ->constrained('users')
                ->onDelete('cascade');

            $table->enum('request_state', ['pending', 'accepted', 'rejected', 'canceled', 'disputed'])->default('pending');
            $table->enum('position', ['challenger', 'opponent'])->nullable();

            $table->unsignedInteger('views')->default(0);

            $table->enum('challenge_status', ['pending', 'won', 'draw', 'loss', 'anomaly'])->default('pending');

            $table->decimal('stake', 10, 2)->default(0.00);
            $table->unsignedInteger('tokens')->default(0);

            $table->foreignId('platform_id')
                ->constrained()
                ->onDelete('cascade');

            $table->string('time_control')->default('5+0'); // e.g. 5 minutes + 0 increment

            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('canceled_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenges');
    }
};
