<?php

namespace App\Infrastructure\Delivery\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class BotInitCommand extends Command
{
    protected static $defaultName = 'telegram:init-webhook';

    public function __construct( $name = null)
    {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        file_get_contents('https://api.telegram.org/bot'.getenv('BOT_API_TOKEN').'/setWebhook?url='.getenv('BOT_WEBHOOK_URL'));
        $ch = curl_init();
        $url = 'https://api.telegram.org/bot'.getenv('BOT_API_TOKEN').'/setWebhook?url='.getenv('BOT_WEBHOOK_URL');
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        $io->success('Bot successfully initialized at url: '. getenv('BOT_WEBHOOK_URL'));
    }
}
