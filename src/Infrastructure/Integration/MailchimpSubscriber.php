<?php


namespace App\Infrastructure\Integration;


use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use Psr\Log\LoggerInterface;

class MailchimpSubscriber
{
    private $logger;
    /** @var Client */
    private $client;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function setClient(Client $client)
    {
        $this->client = $client;
    }

    public function subscribe(string $email): void
    {
        if (getenv('MAILCHIMP_LIST_ID')) {
            $listID = getenv('MAILCHIMP_LIST_ID');
            $token = getenv('MAILCHIMP_API_TOKEN');
            $email = filter_var($email, FILTER_SANITIZE_EMAIL);
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $body = ["email_address" => $email, "status" => "subscribed"];

                $psrrequest = new Request(
                    'POST',
                    "/3.0/lists/{$listID}/members/", [
                    'content-type' => 'application/json',
                ], \GuzzleHttp\json_encode($body));
                try {
                    $this->client->send($psrrequest, [
                        'base_uri' => 'https://'. getenv('MAILCHIMP_ZONE').'.api.mailchimp.com',
                        'auth' => ['Authorize', $token],
                    ]);
                } catch (ClientException $clientException) {
                    $this->logger->warning("subscribing failed", ['error' => $clientException->getMessage()]);
                }
            }
        }
    }

}