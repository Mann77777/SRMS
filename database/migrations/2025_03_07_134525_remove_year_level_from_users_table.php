<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'year_level')) {
                $table->dropColumn('year_level');
            }
        });
    }
    
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'year_level')) {
                $table->string('year_level')->nullable(); // Adjust the column type as needed
            }
        });
    }
};
