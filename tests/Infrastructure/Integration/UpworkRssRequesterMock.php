<?php


namespace App\Tests\Infrastructure\Integration;


use App\Domain\Upwork\UpworkRequesterInterface;
use App\Infrastructure\Integration\UpworkRssRequester;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;

class UpworkRssRequesterMock implements UpworkRequesterInterface
{
    public static $fixtures = [];
    /** @var UpworkRssRequester */
    private $decorated;

    public function __construct(UpworkRequesterInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function fetchUpdates($filter): array
    {
        $responses = array_merge(static::$fixtures,
            [new RequestException("Error Communicating with Server", new Request('GET', 'test'))]);

        $handler = HandlerStack::create(new MockHandler($responses));
        $mockClient = new Client(['handler' => $handler]);

        $this->decorated->setClient($mockClient);

        return $this->decorated->fetchUpdates($filter);
    }
}
