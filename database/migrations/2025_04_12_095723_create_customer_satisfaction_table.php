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
        Schema::create('customer_satisfactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('request_id');
            $table->enum('request_type', ['Student', 'Faculty & Staff'])->comment('Type of request: Student or Faculty & Staff');
            $table->tinyInteger('responsiveness')->comment('Rating from 1-5');
            $table->tinyInteger('reliability')->comment('Rating from 1-5');
            $table->tinyInteger('access_facilities')->comment('Rating from 1-5');
            $table->tinyInteger('communication')->comment('Rating from 1-5');
            $table->tinyInteger('costs')->comment('Rating from 1-5');
            $table->tinyInteger('integrity')->comment('Rating from 1-5');
            $table->tinyInteger('assurance')->comment('Rating from 1-5');
            $table->tinyInteger('outcome')->comment('Rating from 1-5');
            $table->float('average_rating', 3, 2)->comment('Average of all ratings');
            $table->text('additional_comments')->nullable();
            $table->timestamps();
            
            // Note: We can't use a standard foreign key here since the request_id
            // could reference either student_service_requests or faculty_service_requests
            // The combination of request_id and request_type determines the relationship
        });
        
        // Add is_surveyed column to student_service_requests table if it doesn't exist
        if (!Schema::hasColumn('student_service_requests', 'is_surveyed')) {
            Schema::table('student_service_requests', function (Blueprint $table) {
                $table->boolean('is_surveyed')->default(false);
            });
        }
        
        // Add is_surveyed column to faculty_service_requests table if it doesn't exist
        if (!Schema::hasColumn('faculty_service_requests', 'is_surveyed')) {
            Schema::table('faculty_service_requests', function (Blueprint $table) {
                $table->boolean('is_surveyed')->default(false);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We won't drop the is_surveyed columns if they already existed before this migration
        Schema::dropIfExists('customer_satisfactions');
    }
};