<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Message;
use App\Models\Group;
use App\Models\GroupUser;

class MessageSeeder extends Seeder
{
    public function run()
    {
        // Get some users for testing
        $supervisor = User::where('role', 'supervisor')->first();
        $students = User::where('role', 'student')->take(3)->get();

        if (!$supervisor || $students->isEmpty()) {
            $this->command->info('No users found. Please run UserSeeder first.');
            return;
        }

        // Create individual conversations
        foreach ($students as $student) {
            // Create some messages between supervisor and student
            Message::create([
                'sender_id' => $supervisor->id,
                'receiver_id' => $student->id,
                'message' => "Hello {$student->name}, how are you doing with your project?",
                'message_type' => 'text',
                'created_at' => now()->subDays(2)
            ]);

            Message::create([
                'sender_id' => $student->id,
                'receiver_id' => $supervisor->id,
                'message' => "Hi supervisor, I'm making good progress. I have a question about the requirements.",
                'message_type' => 'text',
                'created_at' => now()->subDays(1)
            ]);

            Message::create([
                'sender_id' => $supervisor->id,
                'receiver_id' => $student->id,
                'message' => "Great! What's your question? I'm here to help.",
                'message_type' => 'text',
                'created_at' => now()->subHours(3)
            ]);
        }

        // Create a group conversation
        $group = Group::create([
            'name' => 'Project Team Alpha',
            'description' => 'Discussion group for Project Alpha team members',
            'created_by' => $supervisor->id
        ]);

        // Add members to the group
        $group->members()->attach($supervisor->id);
        foreach ($students as $student) {
            $group->members()->attach($student->id);
        }

        // Add some group messages
        Message::create([
            'sender_id' => $supervisor->id,
            'group_id' => $group->id,
            'message' => "Welcome everyone to the Project Alpha team! Let's work together to make this project successful.",
            'message_type' => 'text',
            'created_at' => now()->subDays(3)
        ]);

        Message::create([
            'sender_id' => $students->first()->id,
            'group_id' => $group->id,
            'message' => "Thank you for having me on the team. I'm excited to contribute!",
            'message_type' => 'text',
            'created_at' => now()->subDays(2)
        ]);

        Message::create([
            'sender_id' => $students->get(1)->id,
            'group_id' => $group->id,
            'message' => "Same here! I've already started working on my assigned tasks.",
            'message_type' => 'text',
            'created_at' => now()->subDays(1)
        ]);

        Message::create([
            'sender_id' => $supervisor->id,
            'group_id' => $group->id,
            'message' => "Excellent! I've scheduled our first team meeting for tomorrow at 2 PM. Please prepare your progress updates.",
            'message_type' => 'text',
            'created_at' => now()->subHours(5)
        ]);

        $this->command->info('Sample messages and groups created successfully!');
    }
} 