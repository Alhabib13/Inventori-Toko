<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OwnerPasswordResetCodeMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $ownerName,
        public ?string $storeName,
        public string $code,
        public int $expiresInMinutes,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Kode Reset Kata Sandi Owner',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.owner-password-reset-code',
        );
    }
}
