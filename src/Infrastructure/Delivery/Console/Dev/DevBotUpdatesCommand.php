<?php

namespace App\Infrastructure\Delivery\Console\Dev;

use App\Application\TelegramBot\Factory\TelegramMessageFactory;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\VarDumper\VarDumper;

/**
 * Get raw updates from Telegram API for testing purposes
 */
class DevBotUpdatesCommand extends Command
{
    protected static $defaultName = 'dev:telegram:updates';
    private $messageBus;
    private $telegramMessageFactory;
    private $client;

    public function __construct(
        MessageBusInterface $messageBus,
        TelegramMessageFactory $telegramMessageFactory,
        Client $client
    ) {
        parent::__construct();
        $this->messageBus = $messageBus;
        $this->telegramMessageFactory = $telegramMessageFactory;
        $this->client = $client;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $response = $this->client->get('https://api.telegram.org/bot' . getenv('BOT_API_TOKEN') . '/getUpdates');
        } catch (ServerException $e) {
            dump($e->getResponse()->getBody()->getContents());
            return 0;
        }

        $updates = json_decode($response->getBody()->getContents(), true);
        $updates = $updates['result'];
        $lastUpdateId = null;
        foreach ($updates as $update) {
            $lastUpdateId = $update['update_id'];
            dump($update);
            $message = $this->telegramMessageFactory->createFromRequestBody(json_encode($update));
            $this->messageBus->dispatch($message);
        }

        if ($lastUpdateId) {
            // Mark update as read
            $this->client->get('https://api.telegram.org/bot' . getenv('BOT_API_TOKEN') . '/getUpdates?offset=' . ($lastUpdateId + 1));
        }
        return 0;
    }
}
