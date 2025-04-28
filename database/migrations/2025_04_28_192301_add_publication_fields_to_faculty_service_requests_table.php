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
            // Add columns after 'installation_notes' or another relevant column
            $table->string('publication_author')->nullable()->after('installation_notes');
            $table->string('publication_editor')->nullable()->after('publication_author');
            $table->date('publication_start_date')->nullable()->after('publication_editor');
            $table->date('publication_end_date')->nullable()->after('publication_start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('faculty_service_requests', function (Blueprint $table) {
            // Drop columns if they exist
            if (Schema::hasColumn('faculty_service_requests', 'publication_author')) {
                $table->dropColumn('publication_author');
            }
            if (Schema::hasColumn('faculty_service_requests', 'publication_editor')) {
                $table->dropColumn('publication_editor');
            }
            if (Schema::hasColumn('faculty_service_requests', 'publication_start_date')) {
                $table->dropColumn('publication_start_date');
            }
            if (Schema::hasColumn('faculty_service_requests', 'publication_end_date')) {
                $table->dropColumn('publication_end_date');
            }
        });
    }
};
