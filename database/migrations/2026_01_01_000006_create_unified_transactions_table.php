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
        Schema::create('unified_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->unsignedBigInteger('from_user_id')->nullable()->index();
            $table->unsignedBigInteger('slot_id')->nullable()->index();
            $table->integer('slot_number')->nullable()->index();
            $table->decimal('amount', 16, 2);
            $table->string('transaction_type', 20)->default('Credit'); // Credit, Debit
            $table->string('category', 50)->index(); // Slot Income, Reserved for Next Slot, Recycle Income, Company Deduction, Slot Activation
            $table->string('status', 30)->default('Completed')->index(); // Completed, Pending, Failed
            $table->string('tx_hash', 100)->nullable()->unique();
            $table->string('from_address', 100)->nullable();
            $table->string('to_address', 100)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unified_transactions');
    }
};
