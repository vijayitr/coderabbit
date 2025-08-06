<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RoleAssignmentSeeder extends Seeder
{
    public function run()
    {
        $role_name = 'superadmin';
        // Create roles if they do not exist (optional)
        if (!Role::where('name', $role_name)->exists()) {
            Role::create(  ['name' => $role_name, 'guard_name' => 'web'],);
        }

        // Find the user to whom you want to assign the role
        $user = User::find(1); // Replace 1 with the ID of the user you want to assign the role to

        if ($user) {
            // Assign the 'admin' role to the user
            $user->assignRole($role_name);
        } else {
            // If the user doesn't exist, create one (optional)
            $newUser = User::create([
                'name' => 'Super Admin User',
                'email' => 'superadmin@mailinator.com',
                'password' => bcrypt('Hello1233#'),
            ]);


            // Assign the 'admin' role to the newly created user
            $newUser->assignRole($role_name);
        }
    }
}
