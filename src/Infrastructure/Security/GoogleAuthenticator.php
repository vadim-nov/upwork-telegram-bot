<?php

namespace App\Infrastructure\Security;

use App\Domain\Core\Entity\User;
use App\Infrastructure\Persistence\UuidGenerator;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2Client;
use KnpU\OAuth2ClientBundle\Client\Provider\GoogleClient;
use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;


class GoogleAuthenticator extends SocialAuthenticator
{
    private $clientRegistry;
    private $router;
    private $em;

    public function __construct(ClientRegistry $clientRegistry, RouterInterface $router, EntityManagerInterface $em)
    {
        $this->clientRegistry = $clientRegistry;
        $this->router = $router;
        $this->em = $em;
    }

    public function supports(Request $request)
    {
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    public function getCredentials(Request $request)
    {
        return $this->fetchAccessToken($this->getClient());
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        /** @var GoogleUser $googleUser */
        $googleUser = $this->getClient()
            ->fetchUserFromToken($credentials);

        $userRepo = $this->em->getRepository(User::class);
        $existingUser = $userRepo->findOneBy(['googleClientID' => $googleUser->getId()]);
        if ($existingUser) {
            return $existingUser;
        }

        $user = $userRepo->findOneBy(['username' => $googleUser->getEmail()]);

        if (!$user) {
            $user = User::createFromOauthProvider(
                UuidGenerator::generate(), $googleUser->getName(), $googleUser->getEmail()
            );
            $userRepo->add($user);
        } else {
            $user->connectGoogleAuthWithExists($googleUser->getId());
        }
        $this->em->flush();

        return $user;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return new RedirectResponse(
            $this->router->generate('settings'),
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }

    /**
     * Called when authentication is needed, but it's not sent.
     * This redirects to the 'login'.
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse(
            $this->router->generate('login'), // might be the site, where users choose their oauth provider
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }

    /**
     * @return GoogleClient
     */
    private function getClient(): OAuth2Client
    {
        return $this->clientRegistry
            ->getClient('google');
    }
}
