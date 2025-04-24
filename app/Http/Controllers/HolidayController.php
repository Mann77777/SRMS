<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class HolidayController extends Controller
{
    /**
     * Display a listing of the holidays.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Get year from request query or use current year
        $year = $request->query('year', now()->year);
        
        // Get all holidays for the year
        $holidays = Holiday::getHolidaysForYear($year);
        
        // Format for display
        $formattedHolidays = $holidays->map(function ($holiday) {
            return [
                'id' => $holiday->id,
                'name' => $holiday->name,
                'date' => $holiday->date ? $holiday->date->format('Y-m-d') : null,
                'formatted_date' => $holiday->date ? $holiday->date->format('F j, Y') : null,
                'description' => $holiday->description,
                'is_recurring' => $holiday->is_recurring,
                'recurring_month' => $holiday->recurring_month,
                'recurring_day' => $holiday->recurring_day,
                'type' => $holiday->type ?? 'holiday',  // Default to 'holiday' if not set
                'start_date' => $holiday->start_date ? $holiday->start_date->format('F j, Y') : null,
                'end_date' => $holiday->end_date ? $holiday->end_date->format('F j, Y') : null,
            ];
        })->sortBy(function($holiday) {
            // Sort by start_date if available, otherwise by date
            return $holiday['start_date'] ?? $holiday['date'] ?? '9999-12-31';
        })->values();
        
        // Return view with holidays
        return view('admin.holidays.index', [
            'holidays' => $formattedHolidays,
            'year' => $year
        ]);
    }

    /**
     * Show the form for creating a new holiday.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $periodTypes = [
            'holiday' => 'Holiday',
            'semestral_break' => 'Semestral Break',
            'exam_week' => 'Exam Week',
            'enrollment_period' => 'Enrollment Period',
            'special_event' => 'Special Event'
        ];
        
        return view('admin.holidays.create', compact('periodTypes'));
    }

    /**
     * Store a newly created holiday in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'description' => 'nullable|string',
            'is_recurring' => 'boolean',
            
            // For single-day events
            'date' => 'required_without:start_date|date|nullable',
            
            // For multi-day periods
            'start_date' => 'required_without:date|date|nullable',
            'end_date' => 'required_with:start_date|date|nullable|after_or_equal:start_date',
            
            // For recurring events
            'recurring_month' => 'required_if:is_recurring,1|integer|min:1|max:12|nullable',
            'recurring_day' => 'required_if:is_recurring,1|integer|min:1|max:31|nullable',
        ]);
        
        try {
            $holiday = Holiday::create($validated);
            
            return redirect()->route('admin.holidays.index')
                ->with('success', 'Academic period created successfully');
                
        } catch (\Exception $e) {
            Log::error('Error creating academic period: ' . $e->getMessage());
            
            return back()->withInput()
                ->with('error', 'Failed to create academic period: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified holiday.
     *
     * @param  \App\Models\Holiday  $holiday
     * @return \Illuminate\Http\Response
     */
    public function show(Holiday $holiday)
    {
        return view('admin.holidays.show', ['holiday' => $holiday]);
    }

    /**
     * Show the form for editing the specified holiday.
     *
     * @param  \App\Models\Holiday  $holiday
     * @return \Illuminate\Http\Response
     */
    public function edit(Holiday $holiday)
    {
        $periodTypes = [
            'holiday' => 'Holiday',
            'semestral_break' => 'Semestral Break',
            'exam_week' => 'Exam Week',
            'enrollment_period' => 'Enrollment Period',
            'special_event' => 'Special Event'
        ];
        
        return view('admin.holidays.edit', compact('holiday', 'periodTypes'));
    }
    /**
     * Update the specified holiday in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Holiday  $holiday
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Holiday $holiday)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'description' => 'nullable|string',
            'is_recurring' => 'boolean',
            
            // For single-day events
            'date' => 'required_without:start_date|date|nullable',
            
            // For multi-day periods
            'start_date' => 'required_without:date|date|nullable',
            'end_date' => 'required_with:start_date|date|nullable|after_or_equal:start_date',
            
            // For recurring events
            'recurring_month' => 'required_if:is_recurring,1|integer|min:1|max:12|nullable',
            'recurring_day' => 'required_if:is_recurring,1|integer|min:1|max:31|nullable',
        ]);
        
        try {
            // Update holiday
            $holiday->update($validated);
            
            return redirect()->route('admin.holidays.index')
                ->with('success', 'Holiday updated successfully');
                
        } catch (\Exception $e) {
            Log::error('Error updating holiday: ' . $e->getMessage());
            
            return back()->withInput()
                ->with('error', 'Failed to update holiday: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified holiday from storage.
     *
     * @param  \App\Models\Holiday  $holiday
     * @return \Illuminate\Http\Response
     */
    public function destroy(Holiday $holiday)
    {
        try {
            $holiday->delete();
            
            return redirect()->route('admin.holidays.index')
                ->with('success', 'Holiday deleted successfully');
                
        } catch (\Exception $e) {
            Log::error('Error deleting holiday: ' . $e->getMessage());
            
            return back()
                ->with('error', 'Failed to delete holiday: ' . $e->getMessage());
        }
    }
    
    /**
     * Import holidays for the current year from a set of common Philippine holidays
     *
     * @return \Illuminate\Http\Response
     */
    public function importCommonHolidays()
    {
        try {
            // Define recurring Philippine holidays
            $commonHolidays = [
                [
                    'name' => 'New Year\'s Day',
                    'is_recurring' => true,
                    'recurring_month' => 1,
                    'recurring_day' => 1,
                ],
                [
                    'name' => 'Araw ng Kagitingan',
                    'is_recurring' => true,
                    'recurring_month' => 4,
                    'recurring_day' => 9,
                ],
                [
                    'name' => 'Labor Day',
                    'is_recurring' => true,
                    'recurring_month' => 5,
                    'recurring_day' => 1,
                ],
                [
                    'name' => 'Independence Day',
                    'is_recurring' => true,
                    'recurring_month' => 6,
                    'recurring_day' => 12,
                ],
                [
                    'name' => 'National Heroes Day',
                    'is_recurring' => true,
                    'recurring_month' => 8,
                    'recurring_day' => 30, // Last Monday of August (simplified)
                ],
                [
                    'name' => 'Bonifacio Day',
                    'is_recurring' => true,
                    'recurring_month' => 11,
                    'recurring_day' => 30,
                ],
                [
                    'name' => 'Christmas Day',
                    'is_recurring' => true,
                    'recurring_month' => 12,
                    'recurring_day' => 25,
                ],
                [
                    'name' => 'Rizal Day',
                    'is_recurring' => true,
                    'recurring_month' => 12,
                    'recurring_day' => 30,
                ],
            ];
            
            // Insert holidays
            foreach ($commonHolidays as $holiday) {
                // Check if holiday already exists
                $exists = Holiday::where('name', $holiday['name'])
                    ->where('is_recurring', true)
                    ->where('recurring_month', $holiday['recurring_month'])
                    ->where('recurring_day', $holiday['recurring_day'])
                    ->exists();
                    
                if (!$exists) {
                    Holiday::create($holiday);
                }
            }
            
            return redirect()->route('admin.holidays.index')
                ->with('success', 'Common Philippine holidays imported successfully');
                
        } catch (\Exception $e) {
            Log::error('Error importing common holidays: ' . $e->getMessage());
            
            return back()
                ->with('error', 'Failed to import common holidays: ' . $e->getMessage());
        }
    }
}