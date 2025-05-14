<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReturnReasonToStudentAndFacultyServiceRequestsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('student_service_requests', function (Blueprint $table) {
            $table->text('return_reason')->nullable();
        });

        Schema::table('faculty_service_requests', function (Blueprint $table) {
            $table->text('return_reason')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('student_service_requests', function (Blueprint $table) {
            $table->dropColumn('return_reason');
        });

        Schema::table('faculty_service_requests', function (Blueprint $table) {
            $table->dropColumn('return_reason');
        });
    }
}
