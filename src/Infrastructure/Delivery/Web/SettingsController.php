<?php

namespace App\Infrastructure\Delivery\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/settings")
 */
class SettingsController extends AbstractController
{
    /**
     * @Route("/", name="settings", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->render('settings/index.html.twig');
    }
}
