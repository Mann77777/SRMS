<?php

namespace App\Utilities;

use App\Models\Holiday;
use Carbon\Carbon;

class DateChecker
{
    /**
     * Check if a given date is a weekend (Saturday or Sunday)
     *
     * @param Carbon $date The date to check
     * @return bool
     */
    public static function isWeekend(Carbon $date)
    {
        return $date->isWeekend();
    }

    /**
     * Check if a given date is a holiday
     *
     * @param Carbon $date The date to check
     * @return bool
     */
    public static function isHoliday(Carbon $date)
    {
        $dateString = $date->format('Y-m-d');

        // Check if the date is a non-recurring holiday
        $isNonRecurringHoliday = Holiday::where('date', $dateString)
            ->where('is_recurring', false)
            ->exists();

        if ($isNonRecurringHoliday) {
            return true;
        }

        // Check if the date is a recurring holiday
        $month = $date->month;
        $day = $date->day;

        $isRecurringHoliday = Holiday::where('is_recurring', true)
            ->where('recurring_month', $month)
            ->where('recurring_day', $day)
            ->exists();

        return $isRecurringHoliday;
    }

    /**
     * Get holiday info for a given date (if it's a holiday)
     *
     * @param Carbon $date The date to check
     * @return \App\Models\Holiday|null
     */
    public static function getHolidayDetailsForDate(Carbon $date)
    {
        $dateString = $date->format('Y-m-d');

        // Check non-recurring holiday first
        $holiday = Holiday::where('date', $dateString)
            ->where('is_recurring', false)
            ->first();

        if ($holiday) {
            return $holiday;
        }

        // Check recurring holiday
        $month = $date->month;
        $day = $date->day;

        return Holiday::where('is_recurring', true)
            ->where('recurring_month', $month)
            ->where('recurring_day', $day)
            ->first();
    }

    /**
     * Check if a given date is a non-working day (weekend or holiday)
     *
     * @param Carbon $date The date to check
     * @return array [bool $isNonWorkingDay, string $type, string|null $holidayName]
     */
    public static function isNonWorkingDay(Carbon $date)
    {
        // Check if the date is a weekend
        if (self::isWeekend($date)) {
            return [
                'isNonWorkingDay' => true,
                'type' => 'weekend',
                'holidayName' => null
            ];
        }

        // Check if the date is a holiday
        $holiday = self::getHolidayDetailsForDate($date);
        if ($holiday) {
            return [
                'isNonWorkingDay' => true,
                'type' => 'holiday',
                'holidayName' => $holiday->name
            ];
        }

        // The date is a working day
        return [
            'isNonWorkingDay' => false,
            'type' => null,
            'holidayName' => null
        ];
    }

    /**
     * Calculate the deadline by adding a number of business days to a start date.
     *
     * @param Carbon $startDate The starting date.
     * @param int $businessDays The number of business days to add.
     * @return Carbon The calculated deadline.
     */
    public static function calculateDeadline(Carbon $startDate, int $businessDays): Carbon
    {
        $currentDate = $startDate->copy();
        $daysAdded = 0;

        while ($daysAdded < $businessDays) {
            $currentDate->addDay();
            if (!self::isNonWorkingDay($currentDate)['isNonWorkingDay']) {
                $daysAdded++;
            }
        }
        return $currentDate;
    }

    /**
     * Count the number of working days between two dates (inclusive of start, exclusive of end by default for remaining).
     * More accurately, counts working days from $startDate up to, but not including, $endDate.
     * If $endDate is before $startDate, it returns a negative count.
     *
     * @param Carbon $startDate The start date.
     * @param Carbon $endDate The end date.
     * @return int The number of working days.
     */
    public static function countWorkingDaysBetween(Carbon $startDate, Carbon $endDate): int
    {
        if ($endDate->isBefore($startDate)) {
            // If deadline has passed, count how many working days ago it was.
            // We count from endDate up to startDate to get a positive number, then negate it.
            $days = 0;
            $current = $endDate->copy();
            while($current->isBefore($startDate)){
                if(!self::isNonWorkingDay($current)['isNonWorkingDay']){
                    $days++;
                }
                $current->addDay();
            }
            return -$days; // Negative because deadline is in the past
        }

        $workingDays = 0;
        $current = $startDate->copy();

        // Iterate from startDate up to (but not including) endDate
        while ($current->isBefore($endDate)) {
            if (!self::isNonWorkingDay($current)['isNonWorkingDay']) {
                $workingDays++;
            }
            $current->addDay();
        }
        return $workingDays;
    }
}
