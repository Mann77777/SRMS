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
            // Remove the 'status' column if it exists
            if (Schema::hasColumn('admins', 'status')) {
                $table->dropColumn('status');
            }
            
            // Add the new availability_status column
            $table->enum('availability_status', ['available', 'busy', 'on_leave'])->default('available')->after('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn('availability_status');
            
            // Optionally re-add the status column if needed
            $table->string('status')->nullable()->after('role');
        });
    }
};