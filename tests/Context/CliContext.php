<?php
/**
 * Created by PhpStorm.
 * User: mitalcoi
 * Date: 21.09.17
 * Time: 12:49
 */

namespace App\Tests\Context;

use App\Command\TransactionEthAutoApproveCommand;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;
use Webmozart\Assert\Assert;

final class CliContext implements Context
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var Application
     */
    private $application;

    /**
     * @var CommandTester
     */
    private $tester;


    private $command;

    /**
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @Given I run command :command
     */
    public function iRunCommand($command)
    {
        $this->application = new Application($this->kernel);
        $this->command = $this->application->find($command);
        $this->tester = new CommandTester($this->command);
        $this->tester->execute(['command' => $this->command]);
    }

    /**
     * @Given I run command :command with:
     */
    public function iRunCommandWith($command, TableNode $args)
    {
        $this->application = new Application($this->kernel);
        $this->command = $this->application->find($command);
        $this->tester = new CommandTester($this->command);
        $params = ['command' => $this->command];
        foreach ($args->getHash() as $hash) {
            $params = array_merge($params, $hash);
        }
        $this->tester->execute($params);
    }

    /**
     * @Then the command should finish successfully
     */
    public function commandSuccess()
    {
        Assert::same($this->tester->getStatusCode(), 0);
    }

    /**
     * @Then I should see command output:
     */
    public function iShouldSeeCommandOutput(PyStringNode $node)
    {
        Assert::contains(trim($this->tester->getDisplay()), trim($node->getRaw()));
    }
}