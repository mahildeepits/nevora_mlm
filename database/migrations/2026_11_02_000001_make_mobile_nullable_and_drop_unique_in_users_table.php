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
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'mobile')) {
            // 1. Safely drop any unique indexes on mobile column
            try {
                $indexes = collect(Schema::getIndexes('users'));
                $mobileUniqueIndexes = $indexes->filter(function ($index) {
                    return in_array('mobile', $index['columns'] ?? []) && !empty($index['unique']) && empty($index['primary']);
                });

                foreach ($mobileUniqueIndexes as $idx) {
                    try {
                        Schema::table('users', function (Blueprint $table) use ($idx) {
                            $table->dropUnique($idx['name']);
                        });
                    } catch (\Throwable $e) {
                        // ignore if already dropped
                    }
                }
            } catch (\Throwable $e) {
                foreach (['users_mobile_unique', 'mobile'] as $indexName) {
                    try {
                        Schema::table('users', function (Blueprint $table) use ($indexName) {
                            $table->dropUnique($indexName);
                        });
                    } catch (\Throwable $e2) {
                        // ignore
                    }
                }
            }

            // 2. Ensure mobile column is nullable (retained in DB, but completely optional)
            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->string('mobile')->nullable()->change();
                });
            } catch (\Throwable $e) {
                // If change() is not supported on this DB driver, nullable remains intact
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'mobile')) {
            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->unique('mobile');
                });
            } catch (\Throwable $e) {
                // ignore
            }
        }
    }
};

