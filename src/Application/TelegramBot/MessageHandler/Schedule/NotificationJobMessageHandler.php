<?php
/**
 * Created by PhpStorm.
 * User: macbook
 * Date: 10/04/2019
 * Time: 22:09
 */

namespace App\Application\TelegramBot\MessageHandler\Schedule;

use App\Domain\Upwork\FilterJobUpdatesFromStopWords;
use App\Domain\Core\Entity\Plan;
use App\Domain\Core\Entity\User;
use App\Application\TelegramBot\Message\Schedule\NotificationJobMessage;
use App\Domain\TelegramBot\TelegramApiInterface;
use App\Application\Upwork\Message\SaveUpworkDataMessage;
use App\Domain\Upwork\UpworkRequesterInterface;
use App\Domain\Upwork\ValueObject\UpworkDataView;
use App\Infrastructure\Form\Dto\UserPlanDto;
use App\Infrastructure\Persistence\Doctrine\Repository\UpworkJobRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Lock\Factory;
use Symfony\Component\Lock\Lock;
use Symfony\Component\Lock\Store\FlockStore;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Webmozart\Assert\Assert;

class NotificationJobMessageHandler implements MessageHandlerInterface
{
    private $upworkRequester;
    private $upworkJobRepository;
    private $messageBus;
    private $telegramApi;
    private $entityManager;
    /** @var UpworkDataView[][] $cache */
    private $cache;
    private $feeds;
    private $logger;

    public function __construct(
        UpworkJobRepository $upworkJobRepository,
        UpworkRequesterInterface $upworkRequester,
        MessageBusInterface $messageBus,
        TelegramApiInterface $telegramApi,
        LoggerInterface $logger,
        EntityManagerInterface $entityManager
    ) {
        $this->upworkRequester = $upworkRequester;
        $this->entityManager = $entityManager;
        $this->upworkJobRepository = $upworkJobRepository;
        $this->messageBus = $messageBus;
        $this->telegramApi = $telegramApi;
        $this->logger = $logger;
        $this->feeds = $this->cache = [];
    }

    public function __invoke(NotificationJobMessage $jobMessage)
    {
        if (!$jobMessage->getPlan()) {
            $users = $this->entityManager->getRepository(User::class)->findTelegramPlanSubscribers(null);
        } else {
            $plan = $this->entityManager->getRepository(Plan::class)->findOneByName($jobMessage->getPlan());
            Assert::notEmpty($plan, 'Invalid plan');
            $users = $this->entityManager->getRepository(User::class)->findTelegramPlanSubscribers($plan);
        }

        /** @var UpworkDataView[][] $feeds */
        $store = new FlockStore();
        $factory = new Factory($store);
        $lock = $factory->createLock('notification-job-message-handler', 10);
        try {
            if ($lock->acquire(true)) {
                $this->fetchUpdates($users, $lock);
            }
        } finally {
            $lock->release();
        }
        $this->entityManager->flush();

        foreach ($this->feeds as $telegramRef => $updatesToSend) {
            try {
                $this->telegramApi->sendBatchMessagesAsync((int)$telegramRef, $updatesToSend, 15);
            } catch (\Exception $e) {
                if ('prod' === getenv('APP_ENV')) {
                    $ravenClient = new \Raven_Client(getenv('SENTRY_DSN'));
                    $ravenClient->captureException($e);
                }
            }
        }
    }

    private function fetchUpdates(array $users, Lock $lock)
    {
        /** @var User[] $users */
        foreach ($users as $user) {
            foreach ($user->getSearches() as $search) {
                try {
                    $searchUrl = $search->getSearchUrl();
                    if ($this->isInCache($searchUrl)) {
                        $userUpdates = $this->cache[$searchUrl];
                    } else {
                        $userUpdates = $this->upworkRequester->fetchUpdates($searchUrl);
                        $userUpdates = (new FilterJobUpdatesFromStopWords())($search, $userUpdates);
                        $this->cache[$searchUrl] = $userUpdates;
                    }
                    foreach ($userUpdates as $userUpdate) {
                        if (null === $this->upworkJobRepository->findBySearchAndLink($search,
                                $userUpdate->getLink())) {
                            if ($user->getTelegramRef()) {
                                $this->feeds[$user->getTelegramRef()][] = $userUpdate;
                            }
                            // Create new UpworkJob
                            $this->messageBus->dispatch(new SaveUpworkDataMessage($search, $userUpdate));
                        }
                    }
                } catch (\Exception $e) {
                    if ('prod' === getenv('APP_ENV')) {
                        $ravenClient = new \Raven_Client(getenv('SENTRY_DSN'));
                        $ravenClient->captureException($e);
                    }
                }
            }
            if (
                !empty($user->getCurrentPlanTo()) &&
                $user->getCurrentPlanTo()->format('Y-m-d H:i:s') < (new \DateTime())->format('Y-m-d H:i:s')
            ) {
                $dto = new UserPlanDto();
                $dto->plan = null;
                $dto->from = null;
                $dto->to = null;
                $user->updatePlanFromDto($dto);
            }
            $lock->refresh();
        }
    }

    private function isInCache(string $searchUrl): bool
    {
        return array_key_exists($searchUrl, $this->cache);
    }
}
