<?php

namespace App\Tests\Context;

use App\Domain\Core\Entity\User;
use App\Domain\Core\Entity\UserSearch;
use App\Domain\TelegramBot\Entity\TelegramMessageLog;
use App\Domain\Upwork\Entity\UpworkJob;
use App\Tests\Infrastructure\Integration\UpworkRssRequesterMock;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behatch\HttpCall\Request;
use Doctrine\Common\Persistence\ManagerRegistry;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\Assert;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * This context class contains the definitions of the steps used by the demo
 * feature file. Learn how to get started with Behat and BDD on Behat's website.
 *
 * @see http://behat.org/en/latest/quick_start.html
 */
class FeatureContext  extends \Behat\MinkExtension\Context\MinkContext implements Context
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    private $manager;
    /**
     * @var Request
     */
    private $request;
    private $JWTTokenManager;

    /**
     * FeatureContext constructor.
     * @param ManagerRegistry $doctrine
     * @param KernelInterface $kernel
     */
    public function __construct(
        KernelInterface $kernel,
        ManagerRegistry $doctrine,
        JWTTokenManagerInterface $JWTTokenManager,
        Request $request
    ) {
        $this->kernel = $kernel;
        $this->manager = $doctrine->getManager();
        $this->request = $request;
        $this->JWTTokenManager = $JWTTokenManager;
    }

    /** @BeforeSuite */
    public static function beforeS()
    {
        try {
            if (file_exists(__DIR__.'/../../var/screens')) {
                exec('rm -r '.__DIR__.'/../../var/screens');
            }
            mkdir(__DIR__.'/../../var/screens');
        } catch (\Exception $e) {
        }
    }

    /**
     * @AfterScenario
     *
     * @param \Behat\Behat\Hook\Scope\AfterScenarioScope $scope
     */
    public function screenshotOnFailure(\Behat\Behat\Hook\Scope\AfterScenarioScope $scope)
    {
        if ($scope->getTestResult()->isPassed() === false) {
            file_put_contents(
                __DIR__.'/../../var/screens/'.time().'.html',
                $this->getSession()->getPage()->getContent()
            );
        }
    }
    /**
     * @Then the telegram message should contain:
     */
    public function theResponseShouldContains(PyStringNode $body)
    {
        $finder = (new Finder())->files()->in($this->getTelegramMessageDir());
        if ($finder->hasResults()) {
            $founded = false;
            foreach ($finder as $file) {
                if (strstr($file->getContents(), $body->getRaw()) !== false) {
                    $founded = true;
                    break;
                }
            }
            Assert::assertTrue($founded, 'Not found this body in messages');
        } else {
            Assert::assertTrue(false, 'No telegram message was sent');
        }
    }

    /**
     * @Then the telegram message should NOT contain:
     */
    public function theResponseShouldNotContain(PyStringNode $body)
    {
        $finder = (new Finder())->files()->in($this->getTelegramMessageDir());
        if ($finder->hasResults()) {
            $founded = false;
            foreach ($finder as $file) {
                if (strstr($file->getContents(), $body->getRaw()) !== false) {
                    $founded = true;
                    break;
                }
            }
            Assert::assertFalse($founded, 'Found this body in messages');
        } else {
            Assert::assertTrue(false, 'No telegram message was sent');
        }
    }

    /**
     * @Given user :arg1 should have search with name :arg2
     */
    public function userShouldHaveSearchWithName($arg1, $arg2)
    {
        $subscriptions = $this->manager->getRepository(UserSearch::class)->findBy(['user' => $arg1]);
        $hasSearch = false;
        foreach ($subscriptions as $subscription) {
            if ($arg2 === $subscription->getSearchName()) {
                $hasSearch = true;
            }
        }
        Assert::assertTrue($hasSearch);
    }

    /**
     * @Given the telegram should send :arg1 messages
     */
    public function theTelegramBotShouldSentMessages($arg1)
    {
        if ($arg1 < 1) {
            Assert::assertFalse(is_dir($this->getTelegramMessageDir()));
            return;
        }
        $finder = (new Finder())->files()->in($this->getTelegramMessageDir());
        Assert::assertCount($arg1, $finder);
    }

    /**
     * @Then the telegram message log with text :text should exist
     */
    public function telegramMessageLogWithTextShouldExist($text)
    {
        $logRepo = $this->manager->getRepository(TelegramMessageLog::class);
        $log = $logRepo->findOneBy(['text' => $text]);
        Assert::assertNotEmpty($log);
    }

    /**
     * @Then upwork job with link :link should exists
     */
    public function theUpworkJobWithLinkShouldExist($link)
    {
        $upworkJobRepo = $this->manager->getRepository(UpworkJob::class);
        $job = $upworkJobRepo->findOneBy(['link' => $link]);
        Assert::assertNotEmpty($job);
    }

    /**
     * @Given entity :arg1 #:arg2 should exist
     */
    public function entityShouldExist($arg1, $arg2)
    {
        $entity = $this->manager->find($arg1, $arg2);
        Assert::assertNotEmpty($entity);
    }

    /**
     * @Given entity :arg1 #:arg2 should not exist
     */
    public function entityShouldNotExist($arg1, $arg2)
    {
        $entity = $this->manager->find($arg1, $arg2);
        Assert::assertEmpty($entity);
    }

    /**
     * @BeforeScenario
     */
    public function cleanupTelegramMessages()
    {
        $fs = new Filesystem();
        $fs->remove($this->getTelegramMessageDir());
    }

    /**
     * @Given upwork response loaded from :fixture
     */
    public function upworkFixture($fixture)
    {
        UpworkRssRequesterMock::$fixtures = [
            new \GuzzleHttp\Psr7\Response(200, ['application/xml'],
                file_get_contents(__DIR__.'/../Infrastructure/Integration/Fixtures/upwork-rss/'.$fixture)),
        ];
    }

    /**
     * @param string $username
     * @Given I am authenticated as :username
     */
    public function iAmAuthenticatedAsWithPassword($username)
    {
        $user = $this->manager->getRepository(User::class)->findOneBy(['username'=>$username]);
        $token=$this->JWTTokenManager->create($user);
        $token = 'Bearer '.$token;
        $this->request->setHttpHeader('Authorization', $token);
        $this->request->setHttpHeader("Content-Type", "application/ld+json");
        $this->request->setHttpHeader("Accept", "application/ld+json");
    }

    private function getTelegramMessageDir()
    {
        return __DIR__.'/../../var/telegram_messages';
    }
}
