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
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // Foreign key to users table (optional)
            $table->json('ms_options')->nullable(); // For storing MS options as JSON
            $table->json('tup_web_options')->nullable(); // For storing TUP web options as JSON
            $table->json('ict_equip_options')->nullable(); // For storing ICT equip options as JSON
            $table->text('ms_other')->nullable(); // Other MS details
            $table->text('tup_web_other')->nullable(); // Other TUP web details
            $table->date('ict_equip_date')->nullable(); // Date for ICT equipment requests
            $table->string('status')->default('Pending'); // Default status
            $table->timestamps();

            // Foreign key constraint (if applicable)
             $table->foreign('user_id')->references('id')->on('users')->onDelete('set null'); //set to null if the user is deleted
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};
