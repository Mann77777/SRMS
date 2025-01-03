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
        Schema::create('faculty_service_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('service_category');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('middle_name');

            $table->string('account_email')->nullable();

            $table->string('data_type')->nullable();
            $table->string('new_data')->nullable();
            $table->string('supporting_document')->nullable();
            $table->text('additional_notes')->nullable();

            $table->json('months')->nullable(); // To store multiple selected months
            $table->integer('year')->nullable(); // To store the selected year

            $table->string('department')->nullable();
            $table->string('college')->nullable();
            $table->string('position')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('address')->nullable();
            $table->string('blood_type')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->string('location')->nullable();
            $table->string('repair_maintenance')->nullable();
            $table->date('preferred_date')->nullable();
            $table->time('preferred_time')->nullable();
            $table->string('author')->nullable();
            $table->string('editor')->nullable();
            $table->date('publication_date')->nullable();
            $table->date('end_publication')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('Pending');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faculty_service_requests');
    }
};