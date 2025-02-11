<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FamilyInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public $senderName;
    public $senderEmail;
    public $relationshipName;
    public $url;

    public function __construct($senderName, $senderEmail, $relationshipName, $url)
    {
        $this->senderName       = $senderName;
        $this->senderEmail      = $senderEmail;
        $this->relationshipName = $relationshipName;
        $this->url              = $url;
    }

    /**
     * Get the message envelope.
    */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invitation',
        );
    }

    /**
     * Get the message content definition.
    */
    public function content(): Content
    {
      return new Content(
          markdown: 'mail.invitefamilymember',
          with: [
              'url'              => $this->url,
              'senderName'       => $this->senderName,
              'senderName'       => $this->senderName,
              'senderEmail'      => $this->senderEmail,
              'relationshipName' => $this->relationshipName,
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
