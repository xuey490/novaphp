<?php
declare(strict_types=1);

namespace App\Services;

class MailerService
{
    public function send(string $to, string $subject, string $body): void
    {
        echo "📧 Sending mail to {$to} | {$subject}\n{$body}\n";
    }
}
