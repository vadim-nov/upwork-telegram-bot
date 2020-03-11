<?php


namespace App\Infrastructure\Integration;

use App\Domain\Upwork\SearchUrlParser;
use App\Domain\Upwork\UpworkRequesterInterface;
use App\Domain\Upwork\ValueObject\UpworkDataView;
use App\Domain\Upwork\ValueObject\UpworkSearchFilter;
use Faker\Factory;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\CacheItem;

class UpworkRssRequester implements UpworkRequesterInterface
{
    /** @var ClientInterface */
    private $client;

    public function setClient(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function fetchUpdates($searchUrl): array
    {
        $searchUrlParser = new SearchUrlParser();
        /** @var UpworkSearchFilter $filter */
        $filter = $searchUrlParser->parse($searchUrl);
        $rssLink = $filter->getRssLink();
        $cacheAdapter = new FilesystemAdapter();
        $faker = Factory::create();
        /** @var CacheItem $item */
        $item = $cacheAdapter->getItem("upwork_rss_feed_".md5($rssLink));

        if (!$item->isHit()) {
            $proxy = null;
            $options = [
                'headers' => [
                    'User-Agent' => $faker->userAgent,
                ],
                'cookie' => true,
                'timeout' => 4,
            ];
            $updates = [];
            if (getenv('UPWORK_RSS_PROXIES')) {
                if (mt_rand(1, 2) === 2) {
                    $proxies = explode(',', getenv('UPWORK_RSS_PROXIES'));
                    $k = array_rand($proxies);
                    $proxy = $proxies[$k];
                    $options['proxy'] = $proxy;
                }
            }
            $response = $this->requestWithRetry($rssLink, $options);
            sleep(mt_rand(1, 2));
            if (200 === $response->getStatusCode()) {
                $content = $response->getBody()->getContents();
                $rssContent = simplexml_load_string($content, 'SimpleXMLElement', \LIBXML_NOCDATA);
                foreach ($rssContent->channel->item as $rssItem) {
                    $updates[] = new UpworkDataView(
                        (string)$rssItem->guid,
                        (string)$rssItem->link,
                        (string)$rssItem->title,
                        (string)$rssItem->description,
                        \DateTime::createFromFormat('D, d M Y H:i:s O', $rssItem->pubDate)
                    );
                }
                $item->set($updates);
                $item->expiresAfter(60);
                $cacheAdapter->save($item);

                return $updates;
            }

            throw new \DomainException('Error ('.$response->getStatusCode().') fetching Upwork with filter: '.$rssLink);
        } else {
            return $item->get();
        }
    }

    private function requestWithRetry(string $rssLink, array $options, int $retryCount = 3)
    {
        $attempts = 0;
        do {
            try {
                $response = $this->client->request('GET', $rssLink, $options);

                return $response;
            } catch (RequestException $e) {
                $attempts++;
                if ($attempts >= $retryCount)
                    throw $e;
                continue;
            }
            break;
        } while($attempts < $retryCount);
    }
}
