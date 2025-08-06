<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ManageDepartmentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('manage_departments')->insert([
            [
                'field_name' => 'Attendance',
                'field_type' => 'dropdown',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'field_name' => 'Operations',
                'field_type' => 'dropdown',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'field_name' => 'Meetings',
                'field_type' => 'dropdown',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'field_name' => 'Production',
                'field_type' => 'dropdown',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'field_name' => 'Training and Support',
                'field_type' => 'dropdown',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'field_name' => 'HR and Payroll',
                'field_type' => 'dropdown',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'field_name' => 'Review and Feedback',
                'field_type' => 'dropdown',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'field_name' => 'Admin and IT',
                'field_type' => 'dropdown',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'field_name' => 'Other',
                'field_type' => 'dropdown',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'field_name' => 'Management',
                'field_type' => 'dropdown',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
