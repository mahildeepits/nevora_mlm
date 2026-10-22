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
        Schema::create('recycle_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('slot_id')->constrained('slots')->onDelete('cascade');
            $table->integer('slot_number')->index();
            $table->integer('cycle_number')->default(1);
            $table->unsignedBigInteger('parent_position_id')->nullable()->index();
            $table->json('child_position_ids')->nullable();
            $table->tinyInteger('leg_position')->default(1);
            $table->integer('level')->default(1);
            $table->integer('received_payments_count')->default(0);
            $table->boolean('is_completed')->default(false)->index();
            $table->timestamps();

            $table->index(['user_id', 'slot_id', 'cycle_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recycle_positions');
    }
};
