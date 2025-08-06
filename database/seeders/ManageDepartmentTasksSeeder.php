<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ManageDepartmentTasksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('manage_department_tasks')->insert([
            // Attendance Tasks
            [
                'field_id' => 1,  // Corresponding to the "Attendance" department (manage_departments table)
                'option_value' => 'Log in',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'field_id' => 1,
                'option_value' => 'Log out',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'field_id' => 1,
                'option_value' => 'Break',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Operations Tasks
            [
                'field_id' => 2,  // Corresponding to the "Operations" department
                'option_value' => 'Work Distribution',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'field_id' => 2,
                'option_value' => 'Daily Huddle',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'field_id' => 2,
                'option_value' => 'Emails and Phone Calls',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Meetings Tasks
            [
                'field_id' => 3,  // Corresponding to the "Meetings" department
                'option_value' => 'Client Meeting',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'field_id' => 3,
                'option_value' => 'Team Meeting',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'field_id' => 3,
                'option_value' => 'Staff Meeting',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'field_id' => 3,
                'option_value' => 'Managers Meeting',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'field_id' => 3,
                'option_value' => 'Leadership Meetings',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'field_id' => 3,
                'option_value' => 'Other Meetings',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Production Tasks
            [
                'field_id' => 4,  // Corresponding to the "Production" department
                'option_value' => 'Working on the following tasks',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Training and Support Tasks
            [
                'field_id' => 5,  // Corresponding to the "Training and Support" department
                'option_value' => 'Orientation New staff',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'field_id' => 5,
                'option_value' => 'Floor Support',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'field_id' => 5,
                'option_value' => 'Query Solving',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'field_id' => 5,
                'option_value' => 'Team Training',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'field_id' => 5,
                'option_value' => 'Staff Training',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'field_id' => 5,
                'option_value' => 'Client Training',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'field_id' => 5,
                'option_value' => 'Self Training',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'field_id' => 5,
                'option_value' => 'Others',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // HR and Payroll Tasks
            [
                'field_id' => 6,  // Corresponding to the "HR and Payroll" department
                'option_value' => 'Conducting Interview',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'field_id' => 6,
                'option_value' => 'Planning Resources',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'field_id' => 6,
                'option_value' => 'Calculating Payroll data',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'field_id' => 6,
                'option_value' => 'Calculating Attendance data',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'field_id' => 6,
                'option_value' => 'Attending Leave and time out requests',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'field_id' => 6,
                'option_value' => 'Attending HR Issues',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Review and Feedback Tasks
            [
                'field_id' => 7,  // Corresponding to the "Review and Feedback" department
                'option_value' => 'Utilization Review',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'field_id' => 7,
                'option_value' => 'Track IT and EOD Review',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'field_id' => 7,
                'option_value' => 'Feedback Session with Staff',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'field_id' => 7,
                'option_value' => 'Feedback Session with Team',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'field_id' => 7,
                'option_value' => 'Feedback Session with Client',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'field_id' => 7,
                'option_value' => 'Feedback Session with Leadership',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'field_id' => 7,
                'option_value' => 'Daily Weekly or Monthly Management Reports',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Admin and IT Tasks
            [
                'field_id' => 8,  // Corresponding to the "Admin and IT" department
                'option_value' => 'Attending Admin Issues',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'field_id' => 8,
                'option_value' => 'Attending IT issues',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'field_id' => 8,
                'option_value' => 'Attending Other Admin and IT related Issues',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Other Tasks
            [
                'field_id' => 9,  // Corresponding to the "Other" department
                'option_value' => 'Attending Other Tasks',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Management Tasks
            [
                'field_id' => 10,
                'option_value' => 'Managing Attendence - FTEs v todays team',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'field_id' => 10,
                'option_value' => 'Managing Work Distribution with priorities',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'field_id' => 10,
                'option_value' => 'Team briefing',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'field_id' => 10,
                'option_value' => 'Review Production v targets - task wise - team member wise',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'field_id' => 10,
                'option_value' => 'Review Quality parameters - task wise - team wise',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'field_id' => 10,
                'option_value' => 'Review Client Deliveries - Task wise - Team wise',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'field_id' => 10,
                'option_value' => 'Client escalations - Listed and resolved v unresoved',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'field_id' => 10,
                'option_value' => 'Support - Floor support - query solving',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
