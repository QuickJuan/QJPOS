<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->select('id', 'pincode')
            ->whereNotNull('pincode')
            ->orderBy('id')
            ->chunkById(500, function ($users) {
                foreach ($users as $user) {
                    if (! static::isHashed($user->pincode)) {
                        DB::table('users')
                            ->where('id', $user->id)
                            ->update(['pincode' => Hash::make($user->pincode)]);
                    }
                }
            });
    }

    public function down(): void
    {
        // No-op: cannot safely revert hashed pincodes.
    }

    private static function isHashed(string $value): bool
    {
        return str_starts_with($value, '$2y$') || str_starts_with($value, '$argon2');
    }
};
