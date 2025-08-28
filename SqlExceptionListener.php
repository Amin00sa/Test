<?php

namespace App\EventListener;

use Doctrine\DBAL\Exception as DBALException;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Psr\Log\LoggerInterface;

class SqlExceptionListener
{
    private $mailer;
    private $logger;

    public function __construct(MailerInterface $mailer, LoggerInterface $logger)
    {
        $this->mailer = $mailer;
        $this->logger = $logger;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        if ($exception instanceof DBALException) {
            $message = $exception->getMessage();
            $sql = method_exists($exception, 'getSQL') ? $exception->getSQL() : 'Requête non disponible';

            // Log dans le fichier
            $this->logger->error('Erreur SQL détectée', [
                'message' => $message,
                'sql' => $sql
            ]);

            // Envoi de l'email
            $email = (new Email())
                ->from('noreply@tonsite.com')
                ->to('admin@tonsite.com')
                ->subject('🚨 Erreur SQL détectée')
                ->text("Une erreur SQL est survenue :\n\nMessage : $message\n\nRequête : $sql");

            $this->mailer->send($email);
        }
    }
}
