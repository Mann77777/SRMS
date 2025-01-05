<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('faculty_service_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->string('service_category');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('college')->nullable();
            $table->string('department')->nullable();
            $table->string('data_type')->nullable();
            $table->text('new_data')->nullable();
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->json('months')->nullable();
            $table->string('year')->nullable();
            $table->string('supporting_document')->nullable();
            $table->text('problem_encountered')->nullable();
            $table->text('repair_maintenance')->nullable();
            $table->date('preferred_date')->nullable();
            $table->time('preferred_time')->nullable();
            $table->string('status')->default('pending');
            $table->json('ms_options')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('faculty_service_requests');
    }
};