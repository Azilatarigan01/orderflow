<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            ['code' => 'IT', 'name' => 'Information & Technology'],
            ['code' => 'FIN', 'name' => 'Finance & Accounting'],
            ['code' => 'OPS', 'name' => 'Operations & Procurement'],
            ['code' => 'HRD', 'name' => 'Human Resources & General Affairs'],
            ['code' => 'MKT', 'name' => 'Marketing & Commercial'],
        ];

        foreach ($departments as $dept) {
            Department::updateOrCreate(['code' => $dept['code']], $dept);
        }
    }
}
