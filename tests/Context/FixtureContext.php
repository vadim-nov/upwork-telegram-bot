<?php

/*
 * This file is part of the API Platform project.
 *
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Tests\Context;


use App\Domain\Core\Entity\Order;
use App\Domain\Core\Entity\Plan;
use App\Domain\Core\Entity\User;
use App\Domain\Core\Entity\UserSearch;
use App\Domain\TelegramBot\Entity\TelegramMessageLog;
use App\Domain\TelegramBot\ValueObject\TelegramUserStructure;
use App\Domain\Upwork\Entity\UpworkJob;
use App\Infrastructure\Persistence\UuidGenerator;
use App\Infrastructure\DomainEvent\DomainEventDispatcher;
use App\Kernel;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behatch\HttpCall\Request;
use Carbon\Carbon;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\SchemaTool;
use Money\Currencies\ISOCurrencies;
use Money\Parser\IntlMoneyParser;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Workflow\Registry;

/**
 * Defines application features from the specific context.
 */
class FixtureContext implements Context
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    private $manager;

    /**
     * @var SchemaTool
     */
    private $schemaTool;

    /**
     * @var array
     */
    private $classes;

    private $encoder;

    /**
     * @var Request\Goutte
     */
    private $request;

    private $workflows;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    private $pa;

    private $domainEvent;

    /**
     * FixtureContext constructor.
     * @param Registry $workflows
     * @param ManagerRegistry $doctrine
     * @param UserPasswordEncoderInterface $encoder
     * @param DomainEventDispatcher $domainEvent
     * @param Request $request
     */
    public function __construct(
        Registry $workflows,
        ManagerRegistry $doctrine,
        UserPasswordEncoderInterface $encoder,
        DomainEventDispatcher $domainEvent,
        Request $request
    ) {
        $this->workflows = $workflows;
        $this->manager = $doctrine->getManager();
        $this->schemaTool = new SchemaTool($this->manager);
        $this->classes = $this->manager->getMetadataFactory()->getAllMetadata();
        $this->request = $request;
        $this->encoder = $encoder;
        $this->pa = PropertyAccess::createPropertyAccessor();
        $this->domainEvent = $domainEvent;
    }

    /** @BeforeSuite */
    public static function before($event)
    {
        $kernel = new Kernel("test", true);
        $kernel->boot();

        $application = new Application($kernel);
        $application->setAutoExit(false);
        try {
            FixtureContext::runConsole(
                $application,
                'doctrine:schema:drop',
                ['--force' => true, '--full-database' => true]
            );
        } catch (\Exception $e) {
        }
        FixtureContext::runConsole($application, 'doctrine:schema:create');

        $kernel->shutdown();
    }

    private static function runConsole(Application $application, $command, $options = [])
    {
        $options['-e'] = 'test';
        $options['-q'] = null;
        $options = array_merge($options, ['command' => $command]);

        return $application->run(new \Symfony\Component\Console\Input\ArrayInput($options));
    }

    /**
     * @BeforeScenario
     */
    public function refreshSchema()
    {
        $this->schemaTool->dropSchema($this->classes);
        $this->schemaTool->createSchema($this->classes);
        UuidGenerator::$id = null;
    }

    /**
     * @Given the following users exists:
     */
    public function theUsersExists(TableNode $table)
    {
        $this->domainEvent->disable();
        foreach ($table->getHash() as $row) {
            if (isset($row['telegram_ref'])) {
                $structure = new TelegramUserStructure((int)$row['telegram_ref']);
                $user = User::createFromTelegram($row['id'], $structure);
            } else {
                $user = new User($row['id'], $row['email']);
                $user->hashPassword($this->encoder, $row['email']);
            }
            if (isset($row['is_dev']) && $row['is_dev']) {
                $user->markAsDev();
            }
            if (!empty($row['current_step'])) {
                $user->setCurrentPlace($row['current_step']);
            }
            if (!empty($row['plan_id'])) {
                $plan = $this->manager->getRepository(Plan::class)->find($row['plan_id']);
                $user->subscribe(
                    $plan,
                    new \DateTimeImmutable('now'),
                    (new \DateTimeImmutable('now'))->add(new \DateInterval('P1D'))
                );
            }
            $this->manager->persist($user);
        }
        $this->manager->flush();
        $this->domainEvent->enable();
    }

    /**
     * @Given the date frozen at :date
     */
    public function theDateFrozenAt($date)
    {
        Carbon::setTestNow($date);
    }

    /**
     * @Given the uuid4 frozen at :id
     */
    public function theUuid4FrozenAt($id)
    {
        UuidGenerator::$id = $id;
    }

    /**
     * @Given the following upwork jobs exists:
     */
    public function theFollowignUpworkJobsExists(TableNode $table)
    {
        $this->domainEvent->disable();
        foreach ($table->getHash() as $row) {
            $userSearch = $this->manager->getRepository(UserSearch::class)->find($row['search_id']);
            $job = new UpworkJob(
                $row['id'],
                $userSearch,
                $row['link'],
                $row['link'],
                $row['title'],
                $row['title'],
                new \DateTime($row['pubDate'])
            );

            $this->manager->persist($job);
        }
        $this->manager->flush();
        $this->domainEvent->enable();
    }


    /**
     * @Given the following plans exists:
     */
    public function thePlansExists(TableNode $table)
    {
        $this->domainEvent->disable();
        foreach ($table->getHash() as $row) {
            $intlParser = new IntlMoneyParser(new \NumberFormatter('en_US', \NumberFormatter::CURRENCY),
                new ISOCurrencies());
            $money = $intlParser->parse($row['money']);
            $plan = new Plan($row['id'], $row['name'], $money, (int)$row['search_count'], (int)$row['update_frequency'],
                (bool)$row['is_visible']);
            $this->manager->persist($plan);
        }
        $this->manager->flush();
        $this->domainEvent->enable();
    }

    /**
     * @Given the following orders exists:
     */
    public function theOrdersExists(TableNode $table)
    {
        $this->domainEvent->disable();
        foreach ($table->getHash() as $row) {
            /** @var User $user */
            $user = $this->manager->getRepository(User::class)->find($row['user']);
            TestCase::assertNotNull($user);
            /** @var Plan $plan */
            $plan = $this->manager->getRepository(Plan::class)->find($row['plan']);
            TestCase::assertNotNull($plan);
            $order = new Order($row['id'], $user, $plan);
            $this->manager->persist($order);
            if (isset($row['is_paid']) && $row['is_paid']) {
                $intlParser = new IntlMoneyParser(new \NumberFormatter('en_US', \NumberFormatter::CURRENCY),
                    new ISOCurrencies());
                $money = $intlParser->parse($row['amount_paid']);
                $order->pay($money);
            }
        }
        $this->manager->flush();
        $this->domainEvent->enable();
    }

    /**
     * @Given the following searches exist:
     */
    public function searchesExist(TableNode $table)
    {
        $this->domainEvent->disable();
        $userRepo = $this->manager->getRepository(User::class);
        foreach ($table->getHash() as $row) {
            /** @var User $user */
            $user = $userRepo->find($row['user_id']);
            /** @var UserSearch $search */
            $user->addSearchPending($row['id'], $row['search_url']);
            if (isset($row['name'])) {
                $search = $user->getPendingSearch();
                $search->setName($row['name']);
            }
            if (isset($row['isPending'])) {
                $search->setIsPending((bool)$row['isPending']);
            }
            $this->manager->flush();
        }
        $this->domainEvent->enable();
    }

    /**
     * @Given the following telegram log messages exist:
     */
    public function telegramLogMessagesExist(TableNode $table)
    {
        $this->domainEvent->disable();
        $userRepo = $this->manager->getRepository(User::class);
        foreach ($table->getHash() as $row) {
            /** @var User $user */
            $user = $userRepo->find($row['user_id']);
            $telegramLog = new TelegramMessageLog(
                $user,
                (string)$row['text'],
                (bool)$row['is_inbound'],
                new \DateTime($row['created_at'])
            );
            $this->manager->persist($telegramLog);
        }
        $this->manager->flush();
        $this->domainEvent->enable();
    }

    /**
     * @Given user :arg1 should have :arg2 settings
     */
    public function userShouldHaveSearchs($arg1, $arg2)
    {
        $subscriptions = $this->manager->getRepository(UserSearch::class)->findBy(['user' => $arg1]);
        TestCase::assertCount($arg2, $subscriptions);
    }

    /**
     * @Given user :arg1 should have :arg2 unpaid orders
     */
    public function userShouldHaveUnpaidOrders($arg1, $arg2)
    {
        /** @var User $user */
        $user = $this->manager->getRepository(User::class)->find($arg1);
        $this->manager->refresh($user);
        foreach ($user->getOrders() as $order) {
            TestCase::assertFalse($order->isPaid());
        }
        TestCase::assertCount($arg2, $user->getOrders());
    }

    /**
     * @Given order #:arg4 should be paid
     */
    public function orderShouldBePaid($arg1)
    {
        /** @var Order $entity */
        $entity = $this->manager->find(Order::class, $arg1);
        $this->manager->refresh($entity);
        TestCase::assertTrue($entity->isPaid());
    }

    /**
     * @Given user :arg1 should have plan :arg2
     */
    public function userShouldHavePlan($arg1, $arg2)
    {
        /** @var User $entity */
        $entity = $this->manager->find(User::class, $arg1);
        $this->manager->refresh($entity);
        if ($arg2 === 'null') {
            TestCase::assertNull($entity->getCurrentPlan());
        } else {
            TestCase::assertEquals($arg2, $entity->getCurrentPlan()->getId());
        }
    }

    /**
     * @Given user should have count :arg2
     */
    public function entityShouldHaveCount($arg2)
    {
        $users = $this->manager->getRepository(User::class)->findAll();
        TestCase::assertCount($arg2, $users);
    }
}
