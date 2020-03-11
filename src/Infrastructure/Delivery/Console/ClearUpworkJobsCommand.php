<?php

namespace App\Infrastructure\Delivery\Console;

use App\Infrastructure\Persistence\Doctrine\Repository\UpworkJobRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ClearUpworkJobsCommand extends Command
{
    protected static $defaultName = 'upworkee:cleanup';
    private $repository;

    public function __construct($name = null, UpworkJobRepository $repository)
    {
        $this->repository = $repository;
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $res = $this->repository->cleanupOldJobs();
        $io->success("Jobs ($res) cleaned");
    }
}
