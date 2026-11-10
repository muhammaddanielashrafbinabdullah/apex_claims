<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = new User([
            'name' => 'Muhammad Daniel Ashraf Bin Abdullah',
            'email' => 'daniel.admin@apex.com.my',
            'password' => 'password',
            'staff_no' => 'ADM1001',
            'designation' => 'HR Manager',
            'department' => 'POP',
        ]);
        $admin->role = 'admin';
        $admin->save();

         $staff = new User([
            'name' => 'Muhammad Daniel Ashraf Bin Abdullah',
            'email' => 'daniel@apex.com.my',
            'password' => 'password',
            'staff_no' => 'APX1001',
            'designation' => 'HR Manager',
            'department' => 'POP',
            'bank_account' => 'MAYBANK 12345',
        ]);
        $staff->role = 'staff';
        $staff->save();

        $staff = new User([
            'name' => 'Badrul Huzaimi Bin Md Aisa',
            'email' => 'badrul@apex.com.my',
            'password' => 'password',
            'staff_no' => 'APX1002',
            'designation' => 'Project Engineer',
            'department' => 'POP',
            'bank_account' => 'BSN 23456',
        ]);
        $staff->role = 'staff';
        $staff->save();

        $staff = new User([
            'name' => 'Ammar Nurzharif Bin Saifulrizam',
            'email' => 'ammar@apex.com.my',
            'password' => 'password',
            'staff_no' => 'APX1003',
            'designation' => 'Network Engineer',
            'department' => 'POP',
            'bank_account' => 'CIMB 34567',
        ]);
        $staff->role = 'staff';
        $staff->save();

        $staff = new User([
            'name' => 'Farhan Nur Akmal Bin Abdul Jalil',
            'email' => 'farhan@apex.com.my',
            'password' => 'password',
            'staff_no' => 'APX1004',
            'designation' => 'IT Engineer',
            'department' => 'POP',
            'bank_account' => 'RHB 45678',
        ]);
        $staff->role = 'staff';
        $staff->save();

        
    }
}
