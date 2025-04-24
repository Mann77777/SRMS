<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Holiday extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'date',
        'description',
        'is_recurring',  // Boolean flag for recurring holidays like Christmas
        'recurring_month', // Month for recurring holidays
        'recurring_day',   // Day for recurring holidays
        'type', // Add this new field: 'holiday', 'semestral_break', 'exam_week', etc.
        'start_date', // For periods that span multiple days
        'end_date',   // End date for periods
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'date' => 'date',
        'is_recurring' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get all holidays for a given year
     * 
     * @param int $year The year to get holidays for
     * @return \Illuminate\Support\Collection Collection of holiday dates
     */
    public static function getHolidaysForYear($year) 
    {
        // Get non-recurring holidays and periods for the specific year
        $nonRecurringItems = self::where(function($query) use ($year) {
                // Regular single-day holidays
                $query->whereYear('date', $year)
                    // Or multi-day periods that overlap with this year
                    ->orWhere(function($q) use ($year) {
                        $q->whereYear('start_date', $year)
                        ->orWhereYear('end_date', $year);
                    });
            })
            ->where('is_recurring', false)
            ->get();
            
        // Get recurring holidays
        $recurringItems = self::where('is_recurring', true)
            ->get()
            ->map(function ($holiday) use ($year) {
                // Create a new date for this year using the recurring month and day
                $newDate = Carbon::createFromDate($year, $holiday->recurring_month, $holiday->recurring_day);
                $holiday->date = $newDate;
                return $holiday;
            });
            
        // Merge both collections and return
        return $nonRecurringItems->merge($recurringItems);
    }

    /**
     * Check if a specific date is a holiday
     * 
     * @param string|Carbon $date The date to check
     * @return bool True if the date is a holiday
     */
    public static function isHoliday($date)
    {
        $checkDate = $date instanceof Carbon ? $date : Carbon::parse($date);
        $year = $checkDate->year;
        
        // Get all holidays for this year
        $holidays = self::getHolidaysForYear($year);
        
        // Check if the date matches any holiday
        foreach ($holidays as $holiday) {
            if ($holiday->date->isSameDay($checkDate)) {
                return true;
            }
        }
        
        return false;
    }
       // Add method to check if date falls within an academic period
    public static function isAcademicPeriod($date, $type)
       {
           $checkDate = $date instanceof Carbon ? $date : Carbon::parse($date);
           $year = $checkDate->year;
           
           // Get all periods of the specified type
           $periods = self::where('type', $type)
               ->where(function($query) use ($year) {
                   $query->whereYear('start_date', $year)
                       ->orWhereYear('end_date', $year)
                       ->orWhere('is_recurring', true);
               })->get();
           
           // For recurring periods, create specific dates for this year
           $periods = $periods->map(function($period) use ($year, $checkDate) {
               if ($period->is_recurring) {
                   // Handle recurring periods (more complex logic here)
                   // You'll need to convert recurring rules to actual dates
               }
               return $period;
           });
           
           // Check if date falls within any period
           foreach ($periods as $period) {
               if ($period->start_date && $period->end_date) {
                   if ($checkDate->between($period->start_date, $period->end_date)) {
                       return true;
                   }
               } else if ($period->date && $period->date->isSameDay($checkDate)) {
                   return true;
               }
           }
           
           return false;
       }
}