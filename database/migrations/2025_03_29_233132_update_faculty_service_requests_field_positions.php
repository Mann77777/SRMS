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
            // Rename position to plantilla_position if it exists
            if (Schema::hasColumn('faculty_service_requests', 'position') && 
                !Schema::hasColumn('faculty_service_requests', 'plantilla_position')) {
                $table->renameColumn('position', 'plantilla_position');
            } 
            // Or create plantilla_position if it doesn't exist
            elseif (!Schema::hasColumn('faculty_service_requests', 'position') && 
                    !Schema::hasColumn('faculty_service_requests', 'plantilla_position')) {
                $table->string('plantilla_position')->nullable();
            }

            // Handle emergency contact fields
            if (Schema::hasColumn('faculty_service_requests', 'emergency_contact')) {
                // Rename to emergency_contact_person if needed
                if (!Schema::hasColumn('faculty_service_requests', 'emergency_contact_person')) {
                    $table->renameColumn('emergency_contact', 'emergency_contact_person');
                }
                
                // Add emergency_contact_number after emergency_contact_person
                if (!Schema::hasColumn('faculty_service_requests', 'emergency_contact_number')) {
                    $table->string('emergency_contact_number')->nullable()->after('emergency_contact_person');
                }
            } 
            // If emergency_contact_person exists but emergency_contact_number doesn't
            elseif (Schema::hasColumn('faculty_service_requests', 'emergency_contact_person') && 
                   !Schema::hasColumn('faculty_service_requests', 'emergency_contact_number')) {
                $table->string('emergency_contact_number')->nullable()->after('emergency_contact_person');
            }
            // If neither exists, create both in the correct order
            elseif (!Schema::hasColumn('faculty_service_requests', 'emergency_contact_person')) {
                $table->string('emergency_contact_person')->nullable();
                $table->string('emergency_contact_number')->nullable()->after('emergency_contact_person');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('faculty_service_requests', function (Blueprint $table) {
            // Reverse the column changes
            if (Schema::hasColumn('faculty_service_requests', 'plantilla_position') && 
                !Schema::hasColumn('faculty_service_requests', 'position')) {
                $table->renameColumn('plantilla_position', 'position');
            } elseif (Schema::hasColumn('faculty_service_requests', 'plantilla_position')) {
                $table->dropColumn('plantilla_position');
            }

            if (Schema::hasColumn('faculty_service_requests', 'emergency_contact_number')) {
                $table->dropColumn('emergency_contact_number');
            }
            
            if (Schema::hasColumn('faculty_service_requests', 'emergency_contact_person') && 
                !Schema::hasColumn('faculty_service_requests', 'emergency_contact')) {
                $table->renameColumn('emergency_contact_person', 'emergency_contact');
            }
        });
    }
};