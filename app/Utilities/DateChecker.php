<?php

namespace App\Utilities;

use App\Models\Holiday;
use Carbon\Carbon;

class DateChecker
{
    /**
     * Check if today is a weekend (Saturday or Sunday)
     *
     * @return bool
     */
    public static function isWeekend()
    {
        $dayOfWeek = Carbon::now()->dayOfWeek;
        return $dayOfWeek === Carbon::SATURDAY || $dayOfWeek === Carbon::SUNDAY;
    }
    
    /**
     * Check if today is a holiday
     *
     * @return bool
     */
    public static function isHoliday()
    {
        $today = Carbon::now()->format('Y-m-d');
        
        // Check if today is a non-recurring holiday
        $isNonRecurringHoliday = Holiday::where('date', $today)
            ->where('is_recurring', false)
            ->exists();
            
        if ($isNonRecurringHoliday) {
            return true;
        }
        
        // Check if today is a recurring holiday
        $month = Carbon::now()->month;
        $day = Carbon::now()->day;
        
        $isRecurringHoliday = Holiday::where('is_recurring', true)
            ->where('recurring_month', $month)
            ->where('recurring_day', $day)
            ->exists();
            
        return $isRecurringHoliday;
    }
    
    /**
     * Get holiday info for today (if it's a holiday)
     *
     * @return \App\Models\Holiday|null
     */
    public static function getTodaysHoliday()
    {
        $today = Carbon::now()->format('Y-m-d');
        
        // Check non-recurring holiday first
        $holiday = Holiday::where('date', $today)
            ->where('is_recurring', false)
            ->first();
            
        if ($holiday) {
            return $holiday;
        }
        
        // Check recurring holiday
        $month = Carbon::now()->month;
        $day = Carbon::now()->day;
        
        return Holiday::where('is_recurring', true)
            ->where('recurring_month', $month)
            ->where('recurring_day', $day)
            ->first();
    }
    
    /**
     * Check if today is a non-working day (weekend or holiday)
     * 
     * @return array [bool $isNonWorkingDay, string $type, string|null $holidayName]
     */
    public static function isNonWorkingDay()
    {
        // Check if today is a weekend
        if (self::isWeekend()) {
            return [
                'isNonWorkingDay' => true,
                'type' => 'weekend',
                'holidayName' => null
            ];
        }
        
        // Check if today is a holiday
        $holiday = self::getTodaysHoliday();
        if ($holiday) {
            return [
                'isNonWorkingDay' => true,
                'type' => 'holiday',
                'holidayName' => $holiday->name
            ];
        }
        
        // Today is a working day
        return [
            'isNonWorkingDay' => false,
            'type' => null,
            'holidayName' => null
        ];
    }
}