<?php


namespace App\Infrastructure\Integration;

use App\Application\TelegramBot\Message\LogChatMessage;
use App\Domain\Core\Entity\User;
use App\Domain\TelegramBot\TelegramApiInterface;
use App\Domain\TelegramBot\UI\TelegramButton;
use App\Domain\Upwork\ValueObject\UpworkDataView;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use function GuzzleHttp\Promise\all as combinePromises;
use Symfony\Component\Messenger\MessageBusInterface;

class TelegramApi implements TelegramApiInterface
{
    /** @var Client */
    private $client;
    private $apiToken;
    private $messageBus;
    private const CHAR_LIMIT = 4096;

    public function __construct(string $apiToken, MessageBusInterface $messageBus)
    {
        $this->apiToken = $apiToken;
        $this->messageBus = $messageBus;
    }

    public function setClient(Client $client)
    {
        $this->client = $client;
    }

    public function sendMessage(int $chatId, string $text, array $keyboard = []): void
    {
        $url = 'https://api.telegram.org/bot'.$this->apiToken.'/sendMessage';
        $text = $this->limitText($text);

        $formParams = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'html',
        ];
        if ($keyboard) {
            $formParams['reply_markup'] = json_encode($keyboard);
        }

        $this->retry(
            function() use ($url, $formParams) {
                try {
                    $this->client->post($url, ['form_params' => $formParams]);
                } catch (ClientException $e){

                }
            }
        );
        $this->messageBus->dispatch(new LogChatMessage($chatId, $text, false));
    }

    private function limitText(string $text): string
    {
        return mb_strlen($text) > self::CHAR_LIMIT
            ? mb_substr($text, 0, self::CHAR_LIMIT - 3) . '...'
            : $text;
    }

    /**
     * @param int $chatId
     * @param UpworkDataView[] $messages
     * @param int $chunkSize
     */
    public function sendBatchMessagesAsync(int $chatId, array $messages, int $chunkSize = 15): void
    {
        if ($chunkSize > 30) {
            throw new \LogicException('To big chunkSize');
        }
        $url = 'https://api.telegram.org/bot'.$this->apiToken.'/sendMessage';
        $messagesChunked = array_chunk($messages, $chunkSize);
        foreach ($messagesChunked as $chunk) {
            $requests = [];
            /** @var UpworkDataView $message */
            foreach ($chunk as $message) {
                $title = html_entity_decode(str_replace(' - Upwork', '', $message->getTitle()));
                $desc = html_entity_decode(str_replace('<br />', PHP_EOL, $message->getDescription()));
                $text = '<b>Title: </b>' . $title . PHP_EOL . '<b>Description: </b>' . $desc;
                $requests[] = $this->client->postAsync($url, [
                    'form_params' => [
                        'chat_id' => $chatId,
                        'text' => $this->limitText($text),
                        'parse_mode' => 'html',
                    ],
                ]);
            }
            $combinedPromise = combinePromises($requests);
            $this->retry(
                function() use ($combinedPromise) {
                    $combinedPromise->wait();
                }
            );
            sleep(1);
        }
    }


    public function removeMessage(int $chatId, int $messageId): void
    {
        $url = 'https://api.telegram.org/bot'.$this->apiToken.'/deleteMessage';
        $this->client->post($url, [
            'form_params' => [
                'chat_id' => $chatId,
                'message_id' => $messageId,
            ]
        ]);
    }

    public function sendHelpAnimation(int $chatId): void
    {
        // Ignore Telegram error "wrong file identifier/HTTP URL specified" for non-production bot
        if (getenv('APP_ENV') !== 'prod') {
            return;
        }
        $url = 'https://api.telegram.org/bot'.$this->apiToken.'/sendAnimation';
        $this->client->post($url, [
            'form_params' => [
                'chat_id' => $chatId,
                'animation' => 'CgADAgADdwMAAi768EnH7SG_3tQHaAI',
            ]
        ]);
    }

    public function buildHelpKeyboard(User $user): array
    {
        if ($user->getSearches()->count()) {
            return [
                'keyboard' => [
                    [TelegramButton::ADD, TelegramButton::HELP],
                    [TelegramButton::LIST, TelegramButton::REMOVE],
                    [TelegramButton::UPGRADE]
                ],
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
            ];
        }

        return [
            'keyboard' => [
                [TelegramButton::ADD, TelegramButton::HELP],
                [TelegramButton::UPGRADE]
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => true,
        ];
    }

    private function retry(callable $action, int $retryCount = 3)
    {
        $attempts = 0;
        do {
            try {
                $action();
            } catch (RequestException $e) {
                if (403 === $e->getResponse()->getStatusCode())  {
                    return;
                }
                $attempts++;
                if ($attempts >= $retryCount)
                    throw $e;
                continue;
            }
            break;
        } while($attempts < $retryCount);
    }
}
