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
            $table->text('dtr_months')->nullable(); // Text field for multiple months
            $table->boolean('dtr_with_details')->default(false); // Checkbox for in/out details
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('faculty_service_requests', function (Blueprint $table) {
            $table->dropColumn(['dtr_months', 'dtr_with_details']);
        });
    }
};
