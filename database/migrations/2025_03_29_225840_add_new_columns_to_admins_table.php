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
        Schema::table('admins', function (Blueprint $table) {
            // Add all missing columns in one migration
            if (!Schema::hasColumn('admins', 'name')) {
                $table->string('name');
            }
            if (!Schema::hasColumn('admins', 'username')) {
                $table->string('username')->unique();
            }
            if (!Schema::hasColumn('admins', 'password')) {
                $table->string('password');
            }
            if (!Schema::hasColumn('admins', 'role')) {
                $table->string('role')->default('Admin');
            }
            // If you need email for admins
            if (!Schema::hasColumn('admins', 'email')) {
                $table->string('email')->unique()->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            // Drop columns in reverse
            $columns = ['name', 'username', 'password', 'role', 'email'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('admins', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};