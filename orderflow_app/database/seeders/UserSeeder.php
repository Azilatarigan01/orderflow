<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $itDept = Department::where('code', 'IT')->first();
        $finDept = Department::where('code', 'FIN')->first();
        $opsDept = Department::where('code', 'OPS')->first();
        $hrdDept = Department::where('code', 'HRD')->first();

        $users = [
            [
                'name' => 'Super Administrator',
                'email' => 'admin@orderflow.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'department_id' => $itDept?->id,
                'phone' => '081234567890',
                'is_active' => true,
            ],
            [
                'name' => 'Anton Wijaya (Manager IT)',
                'email' => 'manager.it@orderflow.com',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'department_id' => $itDept?->id,
                'phone' => '081234567891',
                'is_active' => true,
            ],
            [
                'name' => 'Budi Santoso (Requester IT)',
                'email' => 'requester.it@orderflow.com',
                'password' => Hash::make('password'),
                'role' => 'requester',
                'department_id' => $itDept?->id,
                'phone' => '081234567892',
                'is_active' => true,
            ],
            [
                'name' => 'Dewi Sartika (Procurement Officer)',
                'email' => 'procurement@orderflow.com',
                'password' => Hash::make('password'),
                'role' => 'procurement',
                'department_id' => $opsDept?->id,
                'phone' => '081234567893',
                'is_active' => true,
            ],
            [
                'name' => 'Rina Hendrawan (Finance Officer)',
                'email' => 'finance@orderflow.com',
                'password' => Hash::make('password'),
                'role' => 'finance',
                'department_id' => $finDept?->id,
                'phone' => '081234567894',
                'is_active' => true,
            ],
            [
                'name' => 'Hendra Gunawan (Internal Auditor)',
                'email' => 'auditor@orderflow.com',
                'password' => Hash::make('password'),
                'role' => 'auditor',
                'department_id' => $finDept?->id,
                'phone' => '081234567895',
                'is_active' => true,
            ],
        ];

        foreach ($users as $userData) {
            $user = User::updateOrCreate(['email' => $userData['email']], $userData);

            // Set manager of IT department if Anton Wijaya
            if ($user->email === 'manager.it@orderflow.com' && $itDept) {
                $itDept->update(['manager_id' => $user->id]);
            }
        }
    }
}
