<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use App\Models\TimeEntry;

class LogLogoutTime
{
    /**
     * Handle the event.
     *
     * @param  Logout  $event
     * @return void
     */
    public function handle(Logout $event)
    {
        if ($event->user) {
            TimeEntry::create([
                'user_id' => $event->user->id,
                'status' => 'completed',
                'category' => 'Attendance Activities',
                'sub_category' => 'Attendance',
                'start_time' => now(),
                'comments' => 'Logged Out',
                'end_time' => now(),
                'activity' => 'Timesheet Logout'
            ]);
        }
    }
}
