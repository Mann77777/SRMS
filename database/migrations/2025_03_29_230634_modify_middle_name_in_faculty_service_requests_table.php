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
        Schema::table('faculty_service_requests', function (Blueprint $table) {
            // Modify middle_name to be nullable
            $table->string('middle_name')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('faculty_service_requests', function (Blueprint $table) {
            // Change it back to required
            $table->string('middle_name')->nullable(false)->change();
        });
    }
};