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
        Schema::table('student_service_requests', function (Blueprint $table) {
            // Add assigned UITC Staff ID
            $table->unsignedBigInteger('assigned_uitc_staff_id')->nullable();
            $table->foreign('assigned_uitc_staff_id')
                  ->references('id')
                  ->on('admins')
                  ->onDelete('set null');

            // Add transaction type
            $table->enum('transaction_type', ['Simple Transaction', 'Complex Transaction', 'Highly Technical Transaction'])
                  ->nullable();

            // Add admin notes
            $table->text('admin_notes')->nullable();

            // Modify existing status column or add if not exists
            if (!Schema::hasColumn('student_service_requests', 'status')) {
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

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('student_service_requests', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['assigned_uitc_staff_id']);
            
            // Drop columns
            $table->dropColumn([
                'assigned_uitc_staff_id', 
                'transaction_type', 
                'admin_notes'
            ]);
        });
    }
};
