<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('staff', function (Blueprint $table) {
            // Drop the availability_status column if it exists
            if (Schema::hasColumn('staff', 'availability_status')) {
                $table->dropColumn('availability_status');
            }
        });
    }

    public function down()
    {
        Schema::table('staff', function (Blueprint $table) {
            // Restore the availability_status column if needed
            if (!Schema::hasColumn('staff', 'availability_status')) {
                $table->enum('availability_status', ['available', 'busy', 'on_leave'])
                      ->default('available')
                      ->nullable()
                      ->after('role');
            }
        });
    }
};