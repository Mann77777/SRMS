<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('faculty_service_requests', function (Blueprint $table) {
            // Drop the email column if it exists
            if (Schema::hasColumn('faculty_service_requests', 'email')) {
                $table->dropColumn('email');
            }
        });
    }

    public function down()
    {
        Schema::table('faculty_service_requests', function (Blueprint $table) {
            // Restore the email column if needed
            if (!Schema::hasColumn('faculty_service_requests', 'email')) {
                $table->string('email')->nullable()->after('account_email');
            }
        });
    }
};