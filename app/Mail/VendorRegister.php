<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VendorRegister extends Mailable
{
    use Queueable, SerializesModels;
    private $vendor;
    private $location;
    private $user;
    private $password;

    /**
     * Create a new message instance.
     */
    public function __construct($vendor, $user, $location, $password)
    {
        $this->vendor = $vendor;
        $this->location = $location;
        $this->user = $user;
        $this->password = $password;
        // 
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Account Created',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.registervendor',
            with: [
                'vendor' => $this->vendor,
                'location' => $this->location,
                'user' => $this->user,
                'password' => $this->password
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
        return [];
    }
}
