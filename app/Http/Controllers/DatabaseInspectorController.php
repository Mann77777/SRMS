<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseInspectorController extends Controller
{
    public function getFacultyServiceRequestsColumns()
    {
        // Get the actual database columns
        $columns = Schema::getColumnListing('faculty_service_requests');
        
        // Return as JSON
        return response()->json([
            'columns' => $columns
        ]);
    }
}