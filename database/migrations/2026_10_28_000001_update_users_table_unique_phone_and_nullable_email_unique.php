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
        // 1. Convert any blank or whitespace mobile strings to NULL so unique index won't collide
        DB::table('users')
            ->where('mobile', '')
            ->orWhere('mobile', ' ')
            ->update(['mobile' => null]);

        // 2. Resolve any existing duplicate non-null mobile values so the unique index won't fail with Error 1062
        try {
            $duplicates = DB::table('users')
                ->select('mobile')
                ->whereNotNull('mobile')
                ->where('mobile', '!=', '')
                ->groupBy('mobile')
                ->havingRaw('COUNT(*) > 1')
                ->pluck('mobile');

            foreach ($duplicates as $dup) {
                $ids = DB::table('users')
                    ->where('mobile', $dup)
                    ->orderBy('id', 'asc')
                    ->pluck('id');

                if ($ids->count() > 1) {
                    // Keep the first user's phone, set subsequent duplicate users to null
                    DB::table('users')->whereIn('id', $ids->slice(1))->update(['mobile' => null]);
                }
            }
        } catch (\Throwable $e) {
            // Non-critical, continue
        }

        // 3. Drop unique index from email safely (if it exists under any name)
        try {
            $indexes = collect(Schema::getIndexes('users'));

            $emailUniqueIndexes = $indexes->filter(function ($index) {
                return in_array('email', $index['columns'] ?? []) && !empty($index['unique']) && empty($index['primary']);
            });

            foreach ($emailUniqueIndexes as $idx) {
                try {
                    Schema::table('users', function (Blueprint $table) use ($idx) {
                        $table->dropUnique($idx['name']);
                    });
                } catch (\Throwable $e) {
                    // Already dropped or ignored
                }
            }
        } catch (\Throwable $e) {
            // Fallback for drivers or configurations where getIndexes is not available
            foreach (['users_email_unique', 'email'] as $indexName) {
                try {
                    Schema::table('users', function (Blueprint $table) use ($indexName) {
                        $table->dropUnique($indexName);
                    });
                } catch (\Throwable $e2) {
                    // Index does not exist, ignore
                }
            }
        }

        // 4. Add unique index to mobile only if it doesn't already exist
        $hasMobileUnique = false;
        try {
            $hasMobileUnique = collect(Schema::getIndexes('users'))->contains(function ($index) {
                return in_array('mobile', $index['columns'] ?? []) && !empty($index['unique']);
            });
        } catch (\Throwable $e) {
            $hasMobileUnique = false;
        }

        if (!$hasMobileUnique) {
            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->unique('mobile');
                });
            } catch (\Throwable $e) {
                // If unique index already exists or was created with another name, ignore
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop unique index from mobile safely
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
                } catch (\Throwable $e) {}
            }
        } catch (\Throwable $e) {
            foreach (['users_mobile_unique', 'mobile'] as $indexName) {
                try {
                    Schema::table('users', function (Blueprint $table) use ($indexName) {
                        $table->dropUnique($indexName);
                    });
                } catch (\Throwable $e2) {}
            }
        }

        // Re-add unique index to email if not already unique
        $hasEmailUnique = false;
        try {
            $hasEmailUnique = collect(Schema::getIndexes('users'))->contains(function ($index) {
                return in_array('email', $index['columns'] ?? []) && !empty($index['unique']);
            });
        } catch (\Throwable $e) {
            $hasEmailUnique = false;
        }

        if (!$hasEmailUnique) {
            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->unique('email');
                });
            } catch (\Throwable $e) {}
        }
    }
};
