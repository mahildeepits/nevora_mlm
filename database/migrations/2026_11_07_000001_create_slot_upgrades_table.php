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
        Schema::create('slot_upgrades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('slot_position_id')->nullable()->constrained('slot_positions')->onDelete('set null');
            $table->integer('from_slot_number')->index();
            $table->integer('to_slot_number')->index();
            $table->decimal('reserved_amount', 12, 2)->default(0.00);
            $table->integer('reserved_payments_count')->default(3);
            $table->string('status', 30)->default('pending')->index(); // 'pending', 'completed', 'rejected'
            $table->unsignedBigInteger('admin_id')->nullable()->index();
            $table->string('admin_wallet')->nullable();
            $table->string('user_wallet')->nullable();
            $table->string('tx_hash')->nullable()->index();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'from_slot_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slot_upgrades');
    }
};

