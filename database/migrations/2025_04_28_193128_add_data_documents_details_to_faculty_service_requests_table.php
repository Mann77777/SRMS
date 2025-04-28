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
            // Add column after 'publication_end_date' or another relevant column
            $table->text('data_documents_details')->nullable()->after('publication_end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('faculty_service_requests', function (Blueprint $table) {
            // Drop column if it exists
            if (Schema::hasColumn('faculty_service_requests', 'data_documents_details')) {
                $table->dropColumn('data_documents_details');
            }
        });
    }
};
