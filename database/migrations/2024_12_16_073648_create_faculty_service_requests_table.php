<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacultyServiceRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('faculty_service_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->json('ms_options')->nullable();
            $table->string('attendance_date')->nullable();
            $table->json('attendance_option')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('college')->nullable();
            $table->string('department')->nullable();
            $table->string('position')->nullable();
            $table->date('dob')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('blood_type')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->json('tup_web_options')->nullable();
            $table->string('tup_web_other')->nullable();
            $table->string('location')->nullable();
            $table->json('internet_telephone')->nullable();
            $table->json('ict_equip_options')->nullable();
            $table->string('ict_equip_date')->nullable();
           $table->string('ict_equip_other')->nullable();
            $table->string('data_docs_report')->nullable();
            $table->string('author')->nullable();
            $table->string('editor')->nullable();
            $table->date('publication_date')->nullable();
           $table->date('end_publication')->nullable();
            $table->string('status')->default('Pending'); // Default value
            $table->timestamps();

             // Define Foreign Key Constraint
              $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('faculty_service_requests');
    }
}