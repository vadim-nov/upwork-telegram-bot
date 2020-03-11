<?php

namespace App\Infrastructure\Delivery\Console\Dev;

use App\Domain\Upwork\SearchUrlParser;
use App\Domain\Upwork\UpworkRequesterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Get raw updates from Telegram API for testing purposes
 */
class DevUpworkRssFetcherCommand extends Command
{
    protected static $defaultName = 'dev:upwork:rss';
    private $upworkRequester;

    public function __construct(
        UpworkRequesterInterface $upworkRequester
    ) {
        parent::__construct();
        $this->upworkRequester = $upworkRequester;
    }

    protected function configure()
    {
        $this->addArgument('url', InputArgument::OPTIONAL, 'Url', 'https://www.upwork.com/search/jobs/?q=react&sort=recency');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $searchUrl = $input->getArgument('url');
        $response = $this->upworkRequester->fetchUpdates($searchUrl);
        dump($response);
    }
}
