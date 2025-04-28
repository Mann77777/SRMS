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
        Schema::table('faculty_service_requests', function (Blueprint $table) {
            // Add columns after a specific existing column for better organization, e.g., after 'led_screen_details'
            // If 'led_screen_details' doesn't exist or you prefer appending, remove the ->after() part.
            $table->string('application_name')->nullable()->after('led_screen_details');
            $table->text('installation_purpose')->nullable()->after('application_name');
            $table->text('installation_notes')->nullable()->after('installation_purpose');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('faculty_service_requests', function (Blueprint $table) {
            // Drop columns if they exist
            if (Schema::hasColumn('faculty_service_requests', 'application_name')) {
                $table->dropColumn('application_name');
            }
            if (Schema::hasColumn('faculty_service_requests', 'installation_purpose')) {
                $table->dropColumn('installation_purpose');
            }
            if (Schema::hasColumn('faculty_service_requests', 'installation_notes')) {
                $table->dropColumn('installation_notes');
            }
        });
    }
};
