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
            $table->string('publication_image_path')->nullable()->after('publication_end_date');
            $table->string('publication_file_path')->nullable()->after('publication_image_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('faculty_service_requests', function (Blueprint $table) {
            if (Schema::hasColumn('faculty_service_requests', 'publication_image_path')) {
                $table->dropColumn('publication_image_path');
            }
            if (Schema::hasColumn('faculty_service_requests', 'publication_file_path')) {
                $table->dropColumn('publication_file_path');
            }
        });
    }
};
