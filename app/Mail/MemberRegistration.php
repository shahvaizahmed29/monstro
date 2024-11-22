<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MemberRegistration extends Mailable
{
    use Queueable, SerializesModels;
    private $firstName;
    private $lastName;
    private $programName;
    private $email;
    private $password;
    private $referral;

    /**
     * Create a new message instance.
     */
    public function __construct($firstName, $lastName, $programName, $email, $password, $referral)
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->programName = $programName;
        $this->email = $email;
        $this->password = $password;
        $this->referral = $referral;
        // 
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Registration',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.memberregistration',
            with: [
                'firstName' => $this->firstName,
                'lastName' => $this->lastName,
                'programName' => $this->programName,
                'email' => $this->email,
                'password' => $this->password,
                'referral' => $this->referral,
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
