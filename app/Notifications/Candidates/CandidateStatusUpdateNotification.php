<?php

namespace App\Notifications\Candidates;

use App\Settings\GeneralSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class CandidateStatusUpdateNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $content;
    protected $companyName;
    protected $attachments;

    public function __construct($content, array $attachments = [])
    {
        $this->content = $content;
        $this->companyName = (new GeneralSetting)->company_name;
        $this->attachments = $attachments;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $mailMessage = (new MailMessage)
            ->from(config('mail.from.address'), config('mail.from.name'))
            // ->from(env('MAIL_FROM_ADDRESS'), $this->companyName)
            ->greeting("Dear {$this->content['candidate_name']},");

        // Status-specific content
        switch ($this->content['status']) {
            case 'Interview-Scheduled':
            case 'Interview-to-be-Scheduled':
            case 'Interview-in-Progress':
                $this->addInterviewDetails($mailMessage);
                break;

            case 'Offer-Made':
                $this->addOfferDetails($mailMessage);
                break;

            case 'Hired':
                $this->addHiredDetails($mailMessage);
                break;

            case 'Rejected':
            case 'Rejected-by-Hiring-Manager':
                $this->addRejectionDetails($mailMessage);
                break;

            default:
                $this->addDefaultStatusDetails($mailMessage);
        }

        // Add note if it exists
        if (!empty($this->content['note'])) {
            $mailMessage->line(new HtmlString('<i>Note: ' . $this->content['note'] . '</i>'));
        }

        $mailMessage->salutation(new HtmlString(
            "Best regards,<br/>{$this->companyName}"
        ));

        // Add all attachments
        foreach ($this->attachments as $attachment) {
            $mailMessage->attach($attachment['path'], [
                'as' => $attachment['name'],
                'mime' => 'application/pdf'
            ]);
        }

        return $mailMessage;
    }

    protected function addInterviewDetails($mailMessage)
    {
        $mailMessage->subject("Interview Invitation: {$this->content['position_name']} at {$this->companyName}")
            ->line("Thank you for applying to the {$this->content['position_name']} role at {$this->companyName}. We enjoyed reviewing your application and would like to invite you for an interview!")
            ->line('')
            ->line(new HtmlString('<strong>Interview Details:</strong>'))
            ->line(new HtmlString('📅 <strong>Date:</strong> ' . $this->content['interview_date']))
            ->line(new HtmlString('🕒 <strong>Time:</strong> ' . $this->content['interview_time']));

        // Display either meeting link or location based on type
        if ($this->content['meeting_details']['type'] === 'link') {
            $mailMessage->line(new HtmlString('🔗 <strong>Meeting Link:</strong> <a href="' . $this->content['meeting_details']['value'] . '">Click here to join</a>'));
        } else {
            $mailMessage->line(new HtmlString('📍 <strong>Location:</strong> ' . $this->content['meeting_details']['value']));
        }

        $mailMessage->line(new HtmlString('👤 <strong>Interviewer:</strong> ' . $this->content['interviewer_name']))
            ->line(new HtmlString('⏳ <strong>Duration:</strong> Approximately ' . $this->content['interview_duration'] . ' minutes'))
            ->line('')
            ->line(new HtmlString('<strong>What to Expect:</strong>'))
            ->line('- A discussion about your experience and the role')
            ->line('- Opportunity to ask questions about our team')
            ->line('')
            ->line(new HtmlString('<strong>Next Steps:</strong>'))
            ->line('- Please confirm your availability')
            ->line('- Let us know if you need any accommodations');
    }

    protected function addOfferDetails($mailMessage)
    {
        $mailMessage->subject("Exciting News: Job Offer for {$this->content['position_name']}!")
            ->line("We're thrilled to extend an offer for the <strong>{$this->content['position_name']}</strong> position at <strong>{$this->companyName}</strong>! Your skills and experience stood out, and we believe you'd be a great fit for our team.")
            ->line('')
            ->line(new HtmlString('<strong>Offer Highlights:</strong>'))
            ->line(new HtmlString('💼 <strong>Role:</strong> ' . $this->content['position_name']))
            ->line(new HtmlString('💰 <strong>Compensation:</strong> ' . $this->content['offer_details']))
            ->line(new HtmlString('📅 <strong>Start Date:</strong> ' . ($this->content['start_date'] ?? 'To be determined')))
            ->line('')
            ->line(new HtmlString('<strong>Next Steps:</strong>'))
            ->line('1. Review the attached offer letter')
            ->line('2. Let us know your decision by ' . $this->content['response_deadline'])
            ->line('3. Feel free to reach out with any questions');
    }

    protected function addHiredDetails($mailMessage)
    {
        $mailMessage->subject("Welcome to {$this->companyName}!")
            ->line(new HtmlString("<strong>Welcome aboard!</strong> 🎉 We're excited to confirm your role as <strong>{$this->content['position_name']}</strong>, starting on <strong>{$this->content['start_date']}</strong>."))
            ->line('')
            ->line(new HtmlString('<strong>Here\'s what to expect next:</strong>'))
            ->line('')
            ->line(new HtmlString('📋 <strong>Before Day 1:</strong>'))
            ->line('- Complete your onboarding paperwork')
            ->line('- Set up your company access credentials')
            ->line('')
            ->line(new HtmlString('👋 <strong>First Day:</strong>'))
            ->line("- Arrive at {$this->content['onboarding_location']} at {$this->content['onboarding_time']}")
            ->line("- You'll meet your team and go through orientation")
            ->line('')
            ->line("We're here to make your transition smooth—don't hesitate to ask if you need anything!");
    }

    protected function addRejectionDetails($mailMessage)
    {
        $mailMessage->subject("Update on Your Application for {$this->content['position_name']}")
            ->line("Thank you for taking the time to apply for the {$this->content['position_name']} role and for sharing your background with us. We appreciate the effort you put into your application.")
            ->line('')
            ->line("After careful consideration, we've decided to move forward with another candidate whose experience aligns closely with the current needs of the role. This was a tough decision, and we were impressed by your skills.");

        if (!empty($this->content['feedback'])) {
            $mailMessage->line('')
                ->line(new HtmlString('<strong>Feedback:</strong>'))
                ->line($this->content['feedback']);
        }

        $mailMessage->line('')
            ->line("We encourage you to apply for future openings at {$this->companyName}—we'd love to stay in touch. Wishing you all the best in your job search!");
    }

    protected function addDefaultStatusDetails($mailMessage)
    {
        $mailMessage->subject("Update on Your Application for {$this->content['position_name']}")
            ->line("We wanted to let you know that your application for {$this->content['position_name']} is currently {$this->content['status']}.")
            ->line('')
            ->line(new HtmlString('<strong>What\'s Next?</strong>'))
            ->line('- We\'ll contact you if you progress to the next stage')
            ->line('- Feel free to reach out if you have questions')
            ->line('')
            ->line("Thank you again for your interest in {$this->companyName}!");
    }

    public function toArray($notifiable)
    {
        return [
            // ... (array representation if needed)
        ];
    }
}
