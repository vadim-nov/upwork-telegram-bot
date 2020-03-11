<?php

declare(strict_types=1);

namespace App\Infrastructure\Delivery\Web\Oauth2;

use App\Domain\Core\Entity\User;
use App\Domain\TelegramBot\ValueObject\TelegramUserStructure;
use App\Infrastructure\Integration\TelegramLoginChecker;
use App\Infrastructure\Persistence\Doctrine\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class TelegramLoginController extends AbstractController
{
    private $telegramLoginChecker;
    private $entityManager;
    private $tokenStorage;

    public function __construct(
        TelegramLoginChecker $telegramLoginChecker,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->telegramLoginChecker = $telegramLoginChecker;
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @Route(path="/clb/telegram_login", name="telegram_login")
     */
    public function loginCallback(Request $request)
    {
        $loginCallback = $this->telegramLoginChecker->parseLoginCallback($request->query->all());
        if (!$loginCallback->isValid()) {
            throw new BadRequestHttpException('Invalid telegram data');
        }
        $userRepo = $this->entityManager->getRepository(User::class);
        $user = $userRepo->findByTelegramRef((string)$loginCallback->getUserId());
        if (!$user) {
            $telegramUserStructure = new TelegramUserStructure((int)$loginCallback->getUserId(), [
                'username' => $loginCallback->getUsername(),
                'first_name' => $loginCallback->getFirstName(),
                'last_name' => $loginCallback->getLastName(),
            ]);
            $user = User::createFromTelegram($userRepo->nextIdentity(), $telegramUserStructure);
            $userRepo->add($user);
            $this->entityManager->flush();
        }
        $this->login($user);

        return new RedirectResponse('/');
    }

    private function login(User $user, $firewall = 'main'): void
    {
        $token = new UsernamePasswordToken($user, $user->getPassword(), $firewall, $user->getRoles());
        $this->tokenStorage->setToken($token);
    }
}
