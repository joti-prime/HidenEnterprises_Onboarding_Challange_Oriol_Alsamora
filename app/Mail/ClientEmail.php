<?php

namespace App\Mail;

use App\Facades\EmailTemplate;
use App\Models\EmailHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClientEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(EmailHistory $email)
    {
        $this->email = $email;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: $this->email->subject,
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: EmailTemplate::view(),
            with: [
                'name' => $this->email->user->username ?? '👋',
                'subject' => $this->email->subject,
                'intro' => $this->email->content,
                'button' => $this->email->button,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        $attachments = [];

        if ($this->email->attachment) {
            foreach ($this->email->attachment as $attachment) {
                $attachments[] = Attachment::fromStorage($attachment['path'])->as($attachment['name']);
            }
        }

        return $attachments;
    }
}
