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
        Schema::create('slots', function (Blueprint $table) {
            $table->id();
            $table->integer('slot_number')->unique();
            $table->string('name');
            $table->decimal('entry_fee', 16, 2);
            $table->decimal('reserved_unit_amount', 16, 2)->default(0);
            $table->integer('reserved_count')->default(3);
            $table->decimal('company_deduction', 16, 2)->default(0);
            $table->decimal('net_payout_amount', 16, 2);
            $table->integer('max_recycles')->default(0);
            $table->string('matrix_type')->default('autopool'); // 'working_tree' for Slot 1, 'autopool' for Slots 2-12
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slots');
    }
};
