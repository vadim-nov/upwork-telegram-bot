<?php


namespace App\Infrastructure\Delivery\Web\Oauth2;


use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class FacebookController extends AbstractController
{
    /**
     * Link to this controller to start the "connect" process
     *
     * @Route("/connect/facebook", name="connect_facebook_start")
     */
    public function connectAction(ClientRegistry $clientRegistry)
    {
        return $clientRegistry
            ->getClient('facebook')
            ->redirect([
                'email',
            ]);
    }

    /**
     *
     * @Route("/connect/facebook/check", name="connect_facebook_check")
     */
    public function connectCheckAction()
    {
        throw new \LogicException("Should not be called");
    }

}
