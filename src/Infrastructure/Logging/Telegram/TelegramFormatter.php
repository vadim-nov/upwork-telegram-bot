<?php


namespace App\Infrastructure\Logging\Telegram;


use Monolog\Formatter\NormalizerFormatter;
use Symfony\Component\Yaml\Yaml;

class TelegramFormatter extends NormalizerFormatter
{
    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct(\DATE_ATOM);
    }

    /**
     * Formats a log record.
     *
     * @param array $record A record to format
     * @return mixed The formatted record
     */
    public function format(array $record)
    {
        $output = "{$record['message']}".PHP_EOL;
        if ($record['context']) {
            $output .= PHP_EOL;
            $output .= Yaml::dump($record['context']);
        }
        if ($record['extra']) {
            $output .= PHP_EOL;
            $output .= Yaml::dump($record['extra']);
        }

        return $output;
    }

    /**
     * Formats a set of log records.
     *
     * @param array $records A set of records to format
     * @return mixed The formatted set of records
     */
    public function formatBatch(array $records)
    {
        $message = '';
        foreach ($records as $record) {
            $message .= $this->format($record);
        }

        return $message;
    }
}
