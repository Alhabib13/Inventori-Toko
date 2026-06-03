<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('mail:test {email}', function (string $email): int {
    Mail::raw('Email test dari Sitori berhasil dikirim.', function ($message) use ($email): void {
        $message
            ->to($email)
            ->subject('Test Email Sitori');
    });

    $this->info("Email test dikirim ke {$email}.");

    return 0;
})->purpose('Kirim email test untuk validasi konfigurasi SMTP production');
