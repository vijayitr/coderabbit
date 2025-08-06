<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\TimeEntry;

class LogLoginTime
{
    /**
     * Handle the event.
     *
     * @param  Login  $event
     * @return void
     */
    public function handle(Login $event)
    {
        TimeEntry::create([
            'user_id' => $event->user->id,
            'status' => 'completed',
            'category' => 'Attendance Activities',
            'sub_category' => 'Attendance',
            'start_time' => now(),
            'comments' => 'Logged In',
            'end_time' => now(),
            'activity' => 'Timesheet Login'
        ]);
    }
}
