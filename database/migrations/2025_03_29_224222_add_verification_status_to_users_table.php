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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'verification_status')) {
                $table->string('verification_status')->default('pending_admin')->nullable();
            }
            if (!Schema::hasColumn('users', 'admin_verified')) {
                $table->boolean('admin_verified')->default(0);
            }
            if (!Schema::hasColumn('users', 'status')) {
                $table->string('status')->default('active')->nullable();
            }
        });
    }
    
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['verification_status', 'admin_verified', 'status']);
        });
    }
};
