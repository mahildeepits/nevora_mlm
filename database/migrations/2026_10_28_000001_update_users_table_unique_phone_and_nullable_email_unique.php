<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Convert any blank or empty mobile strings to NULL so unique index won't collide
        DB::table('users')->where('mobile', '')->update(['mobile' => null]);

        Schema::table('users', function (Blueprint $table) {
            // Drop unique index from email
            $table->dropUnique(['email']);

            // Add unique index to mobile / phone number
            $table->unique('mobile');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['mobile']);
            $table->unique('email');
        });
    }
};
