<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class AccountDeletionMail extends Mailable
{
    use Queueable;

    public function __construct(public readonly string $email) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Wedge Matrix Account Has Been Deleted',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.account-deletion',
        );
    }
}
