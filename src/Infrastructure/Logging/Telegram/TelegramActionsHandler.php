<?php


namespace App\Infrastructure\Logging\Telegram;


use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Handler\Curl;
use Monolog\Handler\MissingExtensionException;
use Monolog\Logger;

/**
 * Class TelegramActionsHandler
 * @package App\Infrastructure\Logging\Telegram
 */
class TelegramActionsHandler extends AbstractProcessingHandler
{
    /**
     * @var int Request timeout
     */
    private $timeout;

    /**
     * @param int $level The minimum logging level at which this handler will be triggered
     * @param bool $bubble Whether the messages that are handled can bubble up the stack or not
     *
     * @throws MissingExtensionException If the PHP cURL extension is not loaded
     */
    public function __construct(
        $level = Logger::INFO,
        $bubble = true
    ) {
        if (!extension_loaded('curl')) {
            throw new MissingExtensionException('The cURL PHP extension is required to use the TelegramActionsHandler');
        }
        $this->timeout = 0;
        $this->formatter = new TelegramFormatter();
        parent::__construct($level, $bubble);
    }

    /**
     * Define a timeout to Telegram send message request.
     *
     * @param int $timeout Request timeout
     *
     * @return TelegramActionsHandler
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * Builds the header of the API Call.
     *
     * @param string $content
     *
     * @return array
     */
    protected function buildHeader($content)
    {
        return [
            'Content-Type: application/json',
            'Content-Length: '.strlen($content),
        ];
    }

    /**
     * Builds the body of API call.
     *
     * @param array $record
     *
     * @return string
     */
    protected function buildContent(array $record)
    {
        $content = [
            'chat_id' => getenv('TELEGRAM_LOG_CHAT_ID'),
            'text' => $record['formatted'],
        ];
        if ($this->formatter instanceof TelegramFormatter) {
            $content['parse_mode'] = 'HTML';
        }

        return json_encode($content);
    }

    /**
     * {@inheritdoc}
     *
     * @param array $record
     */
    protected function write(array $record)
    {
        $content = $this->buildContent($record);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->buildHeader($content));
        curl_setopt($ch, CURLOPT_URL,
            sprintf('https://api.telegram.org/bot%s/sendMessage', getenv('TELEGRAM_BOT_LOGGER_TOKEN')));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        Curl\Util::execute($ch);
    }
}
