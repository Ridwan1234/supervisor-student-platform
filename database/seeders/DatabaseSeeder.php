<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Student;
use App\Models\Supervisor;
use App\Models\Project;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Truncate tables to avoid duplicate entries
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::truncate();
        Supervisor::truncate();
        Student::truncate();
        Project::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Create supervisor user
        User::create([
            'name' => 'Dr. John Smith',
            'email' => 'supervisor@example.com',
            'password' => bcrypt('password'),
            'role' => 'supervisor',
        ]);

        // Create supervisor record
        Supervisor::create([
            'user_id' => 1,
            'department' => 'Computer Science',
            'expertise_areas' => json_encode(['Machine Learning', 'Web Development', 'Database Systems']),
        ]);

        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('adminpassword'),
            'role' => 'admin',
        ]);

        // Create student users
        $students = [
            [
                'name' => 'Alice Johnson',
                'email' => 'alice@example.com',
                'password' => bcrypt('password'),
                'role' => 'student',
            ],
            [
                'name' => 'Bob Wilson',
                'email' => 'bob@example.com',
                'password' => bcrypt('password'),
                'role' => 'student',
            ],
            [
                'name' => 'Carol Davis',
                'email' => 'carol@example.com',
                'password' => bcrypt('password'),
                'role' => 'student',
            ],
            [
                'name' => 'David Brown',
                'email' => 'david@example.com',
                'password' => bcrypt('password'),
                'role' => 'student',
            ],
        ];

        foreach ($students as $index => $studentData) {
            $user = User::create($studentData);
            
            // Create student record
            Student::create([
                'user_id' => $user->id,
                'student_id' => 'STU' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'major' => 'Computer Science',
                'year_level' => rand(1, 4),
            ]);
        }

        // Run project seeder
        $this->call([
            ProjectSeeder::class,
        ]);
    }
}
