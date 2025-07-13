<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\Student;
use App\Models\User;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample projects
        $projects = [
            [
                'title' => 'Machine Learning Research Project',
                'description' => 'Research and development of new algorithms for data processing and machine learning applications.',
                'status' => 'in_progress',
                'progress' => 65,
                'start_date' => '2025-01-01',
                'due_date' => '2025-07-15',
                'supervisor_id' => 1,
            ],
            [
                'title' => 'Web Application Development',
                'description' => 'Development of a comprehensive web application for student management system with modern UI/UX.',
                'status' => 'completed',
                'progress' => 100,
                'start_date' => '2024-12-01',
                'due_date' => '2025-06-01',
                'supervisor_id' => 1,
            ],
            [
                'title' => 'Data Analytics Dashboard',
                'description' => 'Implementation of machine learning models for predictive analysis and data visualization.',
                'status' => 'not_started',
                'progress' => 0,
                'start_date' => '2025-02-01',
                'due_date' => '2025-08-01',
                'supervisor_id' => 1,
            ],
            [
                'title' => 'Mobile App Development',
                'description' => 'Cross-platform mobile application development using React Native for educational purposes.',
                'status' => 'in_progress',
                'progress' => 35,
                'start_date' => '2025-01-15',
                'due_date' => '2025-09-10',
                'supervisor_id' => 1,
            ],
            [
                'title' => 'Database Optimization Project',
                'description' => 'Performance optimization and restructuring of existing database systems for better efficiency.',
                'status' => 'on_hold',
                'progress' => 20,
                'start_date' => '2024-11-01',
                'due_date' => '2025-05-01',
                'supervisor_id' => 1,
            ],
        ];

        foreach ($projects as $projectData) {
            $project = Project::create($projectData);
            
            // Assign some students to projects (if students exist)
            $students = Student::take(rand(1, 3))->get();
            if ($students->count() > 0) {
                $project->students()->attach($students->pluck('id'));
            }
        }
    }
}
