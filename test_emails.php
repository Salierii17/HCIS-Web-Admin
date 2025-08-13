<?php

/**
 * Email Testing Script for Recruitment System
 * Run this with: php test_emails.php
 */

require_once __DIR__.'/vendor/autoload.php';

use App\Models\JobCandidates;
use App\Models\User;
use App\Models\Candidates;
use App\Models\candidatePortalInvitation;
use App\Notifications\Candidates\CandidateStatusUpdateNotification;
use App\Notifications\Candidates\CandidatePortalInvitation as CandidatePortalInvitationNotification;
use App\Notifications\SendTrainingNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;

// Bootstrap Laravel
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== EMAIL TESTING SCRIPT ===\n\n";

// Test 1: Training Notification
echo "1. Testing Training Notification...\n";
try {
    $user = User::first();
    if ($user) {
        $user->notify(new SendTrainingNotification('Test Training Package'));
        echo "✅ Training notification sent to: {$user->email}\n";
    } else {
        echo "❌ No users found in database\n";
    }
} catch (Exception $e) {
    echo '❌ Training notification failed: '.$e->getMessage()."\n";
}

echo "\n";

// Test 2: Candidate Status Update
echo "2. Testing Candidate Status Update...\n";
try {
    $candidate = JobCandidates::with('candidateProfile', 'job')->first();
    if ($candidate) {
        $emailContent = [
            'subject' => 'Test Interview Invitation',
            'candidate_name' => $candidate->candidateProfile->full_name ?? 'Test Candidate',
            'status' => 'Interview-Scheduled',
            'position_name' => $candidate->job->postingTitle ?? 'Test Position',
            'interview_date' => 'Monday, January 15, 2024',
            'interview_time' => '10:00 AM',
            'interviewer_name' => 'HR Manager',
            'interview_duration' => '60',
            'meeting_details' => [
                'type' => 'link',
                'value' => 'https://meet.google.com/test-meeting',
            ],
            'note' => 'This is a test email from the system',
        ];

        $candidate->notify(new CandidateStatusUpdateNotification($emailContent));
        echo "✅ Candidate notification sent to: {$candidate->Email}\n";
    } else {
        echo "❌ No job candidates found in database\n";
    }
} catch (Exception $e) {
    echo '❌ Candidate notification failed: '.$e->getMessage()."\n";
}

echo "\n";

// Test 3: Candidate Profile Resource - Portal Invitation
echo "3. Testing Candidate Profile Portal Invitation...\n";
try {
    $candidate = Candidates::first();
    if ($candidate) {
        // Create a test portal invitation
        $invite = candidatePortalInvitation::create([
            'name' => "{$candidate->FirstName} {$candidate->LastName}",
            'email' => $candidate->email,
            'sent_at' => Carbon::now(),
        ]);
        
        // Generate the invitation link
        $invite_link = URL::signedRoute('portal.invite', ['id' => $invite->id]);
        
        // Send the portal invitation notification
        $candidate->notifyNow(new CandidatePortalInvitationNotification($candidate, $invite_link));
        
        echo "✅ Candidate portal invitation sent to: {$candidate->email}\n";
        echo "   Candidate: {$candidate->FirstName} {$candidate->LastName}\n";
        echo "   Candidate ID: {$candidate->CandidateId}\n";
        echo "   Invitation ID: {$invite->id}\n";
    } else {
        echo "❌ No candidates found in database\n";
    }
} catch (Exception $e) {
    echo '❌ Candidate portal invitation failed: '.$e->getMessage()."\n";
}

echo "\n";

// Test 4: Simple Mail Test
// echo "4. Testing Simple Mail...\n";
// try {
//     \Illuminate\Support\Facades\Mail::raw('This is a test email from Laravel Tinker', function ($message) {
//         $message->to('test@example.com')
//                 ->subject('Test Email from Recruitment System');
//     });
//     echo "✅ Simple test email sent\n";
// } catch (Exception $e) {
//     echo "❌ Simple test email failed: " . $e->getMessage() . "\n";
// }

echo "\n=== EMAIL TESTING COMPLETED ===\n";
