<?php


namespace App\Infrastructure\Delivery\Web;


use App\Infrastructure\Persistence\Doctrine\Repository\UpworkJobRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MarkAllJobsAsRead extends AbstractController
{
    /**
     * @Route("/api/upwork_jobs/mark-all-as-read")
     * @param Request $request
     * @param UpworkJobRepository $jobRepository
     * @return JsonResponse
     */
    public function __invoke(Request $request, UpworkJobRepository $jobRepository)
    {
        $jobRepository->markAllJobsAsRead();
        return new JsonResponse();
    }
}
