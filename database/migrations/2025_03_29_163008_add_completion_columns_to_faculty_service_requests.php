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
        Schema::table('faculty_service_requests', function (Blueprint $table) {
            // Check if columns don't exist before adding them
            if (!Schema::hasColumn('faculty_service_requests', 'completion_report')) {
                $table->text('completion_report')->nullable();
            }
            
            if (!Schema::hasColumn('faculty_service_requests', 'actions_taken')) {
                $table->text('actions_taken')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('faculty_service_requests', function (Blueprint $table) {
            $table->dropColumn(['completion_report', 'actions_taken']);
        });
    }
};