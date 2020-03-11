<?php

namespace App\Infrastructure\Delivery\Console;

use App\Application\TelegramBot\Message\Schedule\NotificationJobMessage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

class NotifyCommand extends Command
{
    protected static $defaultName = 'telegram:notify';
    private $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        parent::__construct();
        $this->messageBus = $messageBus;
    }

    protected function configure()
    {
        $this->addOption('plan', null, InputOption::VALUE_REQUIRED, 'Filter users by plan');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $plan = $input->getOption('plan');
        $io->success('Notification job is dispatched');
        $this->messageBus->dispatch(new NotificationJobMessage($plan));
    }
}
