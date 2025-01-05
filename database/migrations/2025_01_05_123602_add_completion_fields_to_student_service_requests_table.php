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
        Schema::table('student_service_requests', function (Blueprint $table) {
            $table->text('completion_report')->nullable();
            $table->text('actions_taken')->nullable();
            $table->enum('completion_status', ['fully_completed', 'partially_completed', 'requires_follow_up'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('student_service_requests', function (Blueprint $table) {
            $table->dropColumn(['completion_status', 'actions_taken']);
        });
    }
};
