<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsCommand(name: 'app:mailer:test', description: 'Envoie un email de test via le mailer configuré')]
class TestMailerCommand extends Command
{
    public function __construct(private MailerInterface $mailer)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('to', InputArgument::REQUIRED, 'Adresse destinataire');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $to = $input->getArgument('to');

        $email = (new Email())
            // ->from('no-reply@' . ($_ENV['DOMAIN'] ?? 'puc.local'))
            ->from('kz.geosm@gmail.com')
            ->to($to)
            ->subject('Test SMTP PDC')
            ->text('Ceci est un email de test envoyé depuis PDC via ' . ($_ENV['MAILER_DSN'] ?? 'DSN inconnu'));

        try {
            $this->mailer->send($email);
            $io->success("Email envoyé à $to");
        } catch (\Throwable $e) {
            $io->error('Échec de l\'envoi : ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}