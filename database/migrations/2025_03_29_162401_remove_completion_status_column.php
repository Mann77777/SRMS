<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Remove only the completion_status column from student_service_requests table
        if (Schema::hasColumn('student_service_requests', 'completion_status')) {
            Schema::table('student_service_requests', function (Blueprint $table) {
                $table->dropColumn('completion_status');
            });
        }

        // Remove only the completion_status column from faculty_service_requests table
        if (Schema::hasColumn('faculty_service_requests', 'completion_status')) {
            Schema::table('faculty_service_requests', function (Blueprint $table) {
                $table->dropColumn('completion_status');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Add the completion_status column back to student_service_requests table
        if (!Schema::hasColumn('student_service_requests', 'completion_status')) {
            Schema::table('student_service_requests', function (Blueprint $table) {
                $table->enum('completion_status', ['fully_completed', 'partially_completed', 'requires_follow_up'])->nullable()->after('status');
            });
        }

        // Add the completion_status column back to faculty_service_requests table
        if (!Schema::hasColumn('faculty_service_requests', 'completion_status')) {
            Schema::table('faculty_service_requests', function (Blueprint $table) {
                $table->enum('completion_status', ['fully_completed', 'partially_completed', 'requires_follow_up'])->nullable()->after('status');
            });
        }
    }
};