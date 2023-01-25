<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerService
{
    private $replyTo;
    public function __construct(private MailerInterface $mailer, $replyTo) {
        $this->replyTo = $replyTo;
    }
    public function sendEmail(
        $to = 'cedric_om10@yahoo.fr',
        $content = '<p>See Twig integration for better HTML integration!</p>',
        $subject = 'Time for Symfony Mailer!'
    ): void
    {
        dd($this->replyTo);
        $email = (new Email())
            ->from('cedric_om10@hotmail.fr')
            ->to($to)
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            ->replyTo($this->replyTo)
            //->priority(Email::PRIORITY_HIGH)
            ->subject($subject)
//            ->text('Sending emails is fun again!')
            ->html($content);
             $this->mailer->send($email);
        // ...
    }

}