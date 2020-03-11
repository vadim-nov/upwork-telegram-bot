<?php


namespace App\Infrastructure\Delivery\Web\Oauth2;


use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class GoogleController extends AbstractController
{
    /**
     * Link to this controller to start the "connect" process
     *
     * @Route("/connect/google", name="connect_google_start")
     */
    public function connectAction(ClientRegistry $clientRegistry)
    {
        return $clientRegistry
            ->getClient('google')
            ->redirect([
                'https://www.googleapis.com/auth/userinfo.email',
            ]);
    }

    /**
     *
     * @Route("/connect/google/check", name="connect_google_check")
     */
    public function connectCheckAction()
    {
        throw new \LogicException("Should not be called");
    }

}
