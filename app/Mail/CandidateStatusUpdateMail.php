<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CandidateStatusUpdateMail extends Mailable
{
    use Queueable, SerializesModels;

    public $content;

    protected $attachmentPath;

    protected $attachmentName;

    /**
     * Create a new message instance.
     */
    public function __construct($content, $attachmentPath = null, $attachmentName = null)
    {
        $this->content = $content;
        $this->attachmentPath = $attachmentPath;
        $this->attachmentName = $attachmentName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->content['subject'],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.candidate_status',
            with: [
                'content' => $this->content,
                'companyName' => config('app.name'),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        if ($this->attachmentPath && file_exists($this->attachmentPath)) {
            return [
                Attachment::fromPath($this->attachmentPath)
                    ->as($this->attachmentName ?? 'document.pdf'),
            ];
        }

        return [];
    }
}
