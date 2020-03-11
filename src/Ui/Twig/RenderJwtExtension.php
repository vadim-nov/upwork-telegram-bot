<?php

namespace App\Ui\Twig;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RenderJwtExtension extends AbstractExtension
{
    private $JWTTokenManager;
    private $security;

    public function __construct(
        JWTTokenManagerInterface $JWTTokenManager,
        Security $security
    ) {
        $this->JWTTokenManager = $JWTTokenManager;
        $this->security = $security;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('render_jwt', [$this, 'renderJwt']),
        ];
    }

    public function renderJwt()
    {
        return base64_encode($this->JWTTokenManager->create($this->security->getUser()));
    }
}
