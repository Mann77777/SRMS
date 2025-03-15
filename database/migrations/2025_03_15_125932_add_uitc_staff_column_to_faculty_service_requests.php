<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUitcStaffColumnToFacultyServiceRequests extends Migration
{
    public function up()
    {
        Schema::table('faculty_service_requests', function (Blueprint $table) {
            // Add column for UITC staff assignment if it doesn't exist
            if (!Schema::hasColumn('faculty_service_requests', 'uitcstaff_id')) {
                $table->unsignedBigInteger('assigned_uitc_staff_id')->nullable();
                $table->foreign('assigned_uitc_staff_id')
                    ->references('id')
                    ->on('admins')
                    ->onDelete('set null');
            }
            
            // Add or modify status column to ensure it exists
            if (!Schema::hasColumn('faculty_service_requests', 'status')) {
                $table->string('status')->default('Pending');
            }
            
            // Add transaction type column
            if (!Schema::hasColumn('faculty_service_requests', 'transaction_type')) {
                $table->enum('transaction_type', ['Simple Transaction', 'Complex Transaction', 'Highly Technical Transaction'])
                    ->nullable();
            }
            
            // Add notes column
            if (!Schema::hasColumn('faculty_service_requests', 'admin_notes')) {
                $table->text('admin_notes')->nullable();
            }

              // Modify existing status column or add if not exists
              if (!Schema::hasColumn('faculty_service_requests', 'status')) {
                $table->enum('status', [
                    'Pending', 
                    'Assigned', 
                    'In Progress', 
                    'Resolved', 
                    'on_hold', 
                    'Rejected'
                ])->default('Pending');
            }
    
        });
    }

    public function down()
    {
        Schema::table('faculty_service_requests', function (Blueprint $table) {
            if (Schema::hasColumn('faculty-service_requests', 'assigned_uitc_staff_id'))
                // Drop the added columns
                $table->dropForeign(['assigned_uitc_staff_id']);
                $table->dropColumn(['assigned_uitc_staff_id']);
            // Note: We're not dropping status as it might have existed before

            // Drop other columns if they exist
            if (Schema::hasColumn('faculty_service_requests', 'transaction_type')) {
                $table->dropColumn('transaction_type');
            }
            
            if (Schema::hasColumn('faculty_service_requests', 'admin_notes')) {
                $table->dropColumn('admin_notes');
            }
        });
    }
}