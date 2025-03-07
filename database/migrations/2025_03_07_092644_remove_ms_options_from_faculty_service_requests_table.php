<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('faculty_service_requests', function (Blueprint $table) {
            $table->dropColumn('ms_options');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('faculty_service_requests', function (Blueprint $table) {
            $table->json('ms_options')->nullable(); // Adjust the type as needed
        });
    }    
};
