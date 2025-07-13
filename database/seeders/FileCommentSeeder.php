<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\File;
use App\Models\FileComment;
use App\Models\User;

class FileCommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $files = File::all();
        $users = User::all();

        if ($files->isEmpty() || $users->isEmpty()) {
            return;
        }

        $sampleComments = [
            [
                'content' => 'This is a great document! Very well structured.',
                'is_resolved' => false,
            ],
            [
                'content' => 'I found a typo on page 3. Should be "implementation" instead of "implemantation".',
                'is_resolved' => true,
            ],
            [
                'content' => 'The formatting looks good. Ready for review.',
                'is_resolved' => false,
            ],
            [
                'content' => 'Could you add more details to section 2.1?',
                'is_resolved' => false,
            ],
            [
                'content' => 'This file needs to be updated with the latest data.',
                'is_resolved' => false,
            ],
        ];

        $sampleReplies = [
            'Thanks for catching that! I\'ll fix it right away.',
            'I\'ve updated the section with more details.',
            'The latest data has been added to the file.',
            'I\'ll review and update the formatting.',
            'Good catch! I\'ve corrected the typo.',
        ];

        foreach ($files as $file) {
            // Add 2-4 comments per file
            $numComments = rand(2, 4);
            
            for ($i = 0; $i < $numComments; $i++) {
                $commentData = $sampleComments[array_rand($sampleComments)];
                $user = $users->random();
                
                $comment = FileComment::create([
                    'file_id' => $file->id,
                    'user_id' => $user->id,
                    'content' => $commentData['content'],
                    'is_resolved' => $commentData['is_resolved'],
                    'resolved_at' => $commentData['is_resolved'] ? now() : null,
                    'resolved_by' => $commentData['is_resolved'] ? $users->random()->id : null,
                ]);

                // Add 1-2 replies to some comments
                if (rand(0, 1)) {
                    $numReplies = rand(1, 2);
                    
                    for ($j = 0; $j < $numReplies; $j++) {
                        $replyUser = $users->random();
                        $replyContent = $sampleReplies[array_rand($sampleReplies)];
                        
                        FileComment::create([
                            'file_id' => $file->id,
                            'user_id' => $replyUser->id,
                            'parent_id' => $comment->id,
                            'content' => $replyContent,
                            'is_resolved' => false,
                        ]);
                    }
                }
            }
        }
    }
}
