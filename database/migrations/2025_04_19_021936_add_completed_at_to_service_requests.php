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
        // For student service requests
        Schema::table('student_service_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('student_service_requests', 'completed_at')) {
                $table->timestamp('completed_at')->nullable();
            }
        });

        // For faculty service requests
        Schema::table('faculty_service_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('faculty_service_requests', 'completed_at')) {
                $table->timestamp('completed_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_service_requests', function (Blueprint $table) {
            $table->dropColumn('completed_at');
        });

        Schema::table('faculty_service_requests', function (Blueprint $table) {
            $table->dropColumn('completed_at');
        });
    }
};