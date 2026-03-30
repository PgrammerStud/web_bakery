<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class ContactEmailService
{
    public function __construct(
        private MailerInterface $mailer
    ) {}

    /**
     * Send contact form email to admin
     */
    public function sendContactNotificationEmail(
        string $name,
        string $email,
        string $phone,
        string $subject,
        string $message,
        bool $subscribe
    ): void {
        $templatedEmail = (new TemplatedEmail())
            ->from(new Address('desireeadie143000@gmail.com', 'Catalbas Bakery')) // Change this to verified sender
            ->to(new Address('desireeadie143000@gmail.com'))
            ->subject('New Contact Form Submission from ' . $name . ': ' . $subject)
            ->htmlTemplate('contact/email_notification.html.twig')
            ->context([
                'name' => $name,
                'userEmail' => $email,
                'phone' => $phone,
                'subject' => $subject,
                'message' => $message,
                'subscribe' => $subscribe ? 'Yes' : 'No',
            ]);

        $this->mailer->send($templatedEmail);
    }

    /**
     * Send confirmation email to the user
     */
    public function sendConfirmationEmail(
        string $name,
        string $userEmail
    ): void {
        $templatedEmail = (new TemplatedEmail())
            ->from(new Address('desireeadie143000@gmail.com', 'Catalbas Bakery'))
            ->to(new Address($userEmail))
            ->subject('We Received Your Message - Catalbas Bakery')
            ->htmlTemplate('contact/email_confirmation.html.twig')
            ->context([
                'name' => $name,
                'userEmail' => $userEmail,
            ]);

        $this->mailer->send($templatedEmail);
    }
}
