<?php

namespace App\Infrastructure\Delivery\Console;

use App\Application\TelegramBot\Message\Schedule\HelpLostTelegramUsersMessage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

class HelpLostTelegramUsers extends Command
{
    protected static $defaultName = 'telegram:help';
    private $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        parent::__construct();
        $this->messageBus = $messageBus;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->success('Helping lost users job is dispatched');
        $this->messageBus->dispatch(new HelpLostTelegramUsersMessage());
    }
}
