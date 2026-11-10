<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = new User([
            'name' => 'Apex Admin',
            'email' => 'admin@apex.test',
            'password' => 'password',
            'staff_no' => 'ADM001',
            'designation' => 'HR Manager',
            'department' => 'Human Resources',
        ]);
        $admin->role = 'admin';
        $admin->save();

        $staff = new User([
            'name' => 'Ayuni Binti Ahmad',
            'email' => 'ayuni@apex.test',
            'password' => 'password',
            'staff_no' => 'APX1024',
            'designation' => 'Network Engineer',
            'department' => 'Operations',
            'bank_account' => 'Maybank 1140 2233 4455',
        ]);
        $staff->role = 'staff';
        $staff->save();
    }
}
