<?php

namespace App\Helpers;

use App\Models\Setting;
use App\Models\TimeEntry;
use Carbon\Carbon;
use DB;

class TimeHelper
{
    // Function to check if users are late based on office timing, user IDs, and date
    public static function attendanceData($userIds = [], $startDate = '', $endDate = '')
    {
        $user = auth()->guard('web')->user();
        if (is_null($user) || !$user->can('dashboard.show_attendance')) {
            return [
                "onTime" =>  0,
                "late" =>  0,
                "noRecord" =>  1
            ];
        }
        // Get office start time
        $officeTiming = Setting::where('setting_name', 'Office Timing')->value('setting_value');
        if (!$officeTiming) {
            return response()->json(['error' => 'Office timing not set!'], 400);
        }

        $officialStartTime = Carbon::createFromFormat('g:i A', explode(' - ', $officeTiming)[0]);

        // Fetch login records
        $loginRecords = TimeEntry::selectRaw('DATE(created_at) as login_date, MIN(created_at) as first_login_time, user_id')
            ->where('activity', 'Timesheet Login')
            ->when(!empty($userIds), function($q) use ($userIds) {
                $q->whereIn('user_id', $userIds);
            })
            ->when(!empty($startDate) && !empty($endDate), function($q) use ($startDate, $endDate) {
                $startDate = date('Y-m-d 00:00:00', strtotime($startDate));
                $endDate = date('Y-m-d 23:59:59', strtotime($endDate));
                // Apply date range filter
                if ($startDate !== $endDate) {
                    $q->whereBetween('created_at', [$startDate, $endDate]);
                } else {
                    $q->whereDate('created_at', $startDate);
                }
            })
            ->groupBy('login_date', 'user_id')
            ->get();


        // Calculate attendance stats
        $stats = ['onTime' => 0, 'late' => 0, 'noRecord' => 0];

        $attendanceData = $loginRecords->map(function ($record) use ($officialStartTime, &$stats) {
            $firstLoginTime = Carbon::parse($record->first_login_time);
            $status = $firstLoginTime->greaterThan($officialStartTime) ? 'Late' : 'On Time';
            $stats[$status === 'Late' ? 'late' : 'onTime']++;

            return [
                'user_id' => $record->user_id,
                'status' => $status,
                'login_time' => $firstLoginTime->toTimeString(),
                'first_login_time' => $record->first_login_time,
            ];
        });

        // Count missing records
        if ($loginRecords->isEmpty()) {
            $stats['noRecord']++;
        }

        return $stats;
    }

    public static function calculateIdleTime($userIds = [], $startDate = '', $endDate = '')
    {
        $user = auth()->guard('web')->user();
        if (is_null($user) || !$user->can('dashboard.show_idle_time')) {
            return [
                    "idle_data_arr" => [],
                    "labels" => [],
                    "data" => []
                ];
        }

        // Query the TimeEntry table where activity is 'Idle'
        $query = TimeEntry::where('activity', 'Idle')
            ->select(
                'user_id',
                'comments',
                'break_option',
                DB::raw('SUM(TIMESTAMPDIFF(SECOND, created_at, IFNULL(updated_at, NOW()))) / 60 AS total_idle_time'),
                DB::raw('TIMESTAMPDIFF(SECOND, start_time, end_time) / 60 AS time_diff')
            )
            ->groupBy('user_id', 'comments', 'break_option', 'start_time', 'end_time');

        // Apply filters if necessary
        if (!empty($userIds)) {
            $query->whereIn('user_id', $userIds);
        }

        // If a date is provided, filter by the date
        if (!empty($startDate) && !empty($endDate)) {
            $endDate = date('Y-m-d 23:59:59', strtotime($endDate));
            $startDate = date('Y-m-d 00:00:00', strtotime($startDate));
            if ($startDate != $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            } else {
                $query->whereDate('created_at', $startDate);
            }
        }

        // Retrieve data
        $idleTimes = $query->get();

        // Prepare chart data
        $idleData = $idleTimes->map(function ($time) {
            return [
                'user_id' => $time->user_id,
                'total_idle_time' => (int) $time->time_diff,
                'display_text' => $time->comments ?: $time->break_option,
            ];
        })->toArray();

        return [
            'idle_data_arr' => $idleData,
            'labels' => array_column($idleData, 'total_idle_time'),
            'data' => array_column($idleData, 'display_text'),
        ];
    }

}
