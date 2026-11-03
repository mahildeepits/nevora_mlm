<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Allows double-entry bookkeeping (both Debit for Payer and Credit for Payee with same tx_hash).
     */
    public function up(): void
    {
        if (Schema::hasTable('unified_transactions') && Schema::hasColumn('unified_transactions', 'tx_hash')) {
            // 1. Safely drop any unique indexes on tx_hash column
            try {
                $indexes = collect(Schema::getIndexes('unified_transactions'));
                $txHashUniqueIndexes = $indexes->filter(function ($index) {
                    return in_array('tx_hash', $index['columns'] ?? []) && !empty($index['unique']) && empty($index['primary']);
                });

                foreach ($txHashUniqueIndexes as $idx) {
                    try {
                        Schema::table('unified_transactions', function (Blueprint $table) use ($idx) {
                            $table->dropUnique($idx['name']);
                        });
                    } catch (\Throwable $e) {}
                }
            } catch (\Throwable $e) {
                foreach (['unified_transactions_tx_hash_unique', 'tx_hash'] as $name) {
                    try {
                        Schema::table('unified_transactions', function (Blueprint $table) use ($name) {
                            $table->dropUnique($name);
                        });
                    } catch (\Throwable $ex) {}
                }
            }

            // 2. Ensure non-unique index on tx_hash exists for fast lookup performance
            try {
                Schema::table('unified_transactions', function (Blueprint $table) {
                    $table->index('tx_hash');
                });
            } catch (\Throwable $e) {}
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('unified_transactions') && Schema::hasColumn('unified_transactions', 'tx_hash')) {
            try {
                Schema::table('unified_transactions', function (Blueprint $table) {
                    $table->unique('tx_hash');
                });
            } catch (\Throwable $e) {}
        }
    }
};
