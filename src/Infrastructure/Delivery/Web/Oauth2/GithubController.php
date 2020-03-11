<?php


namespace App\Infrastructure\Delivery\Web\Oauth2;


use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class GithubController extends AbstractController
{
    /**
     * Link to this controller to start the "connect" process
     *
     * @Route("/connect/github", name="connect_github_start")
     */
    public function connectAction(ClientRegistry $clientRegistry)
    {
        return $clientRegistry
            ->getClient('github')
            ->redirect([
                'user:email',
            ]);
    }

    /**
     *
     * @Route("/connect/github/check", name="connect_github_check")
     */
    public function connectCheckAction()
    {
        throw new \LogicException("Should not be called");
    }

}
