<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Role column is already included in the users table migration (000000).
// This migration is kept for backwards compatibility only.
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role', 50)->default('customer')->after('email');
            });
        }
    }

    public function down(): void
    {
        // intentionally left blank – role column is part of the base users table
    }
};
