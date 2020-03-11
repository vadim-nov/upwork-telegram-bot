<?php


namespace App\Infrastructure\ApiPlatform\DataTransformer;


use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use App\Domain\Core\Dto\UpgradeRequestInput;
use App\Domain\Core\Entity\Order;
use App\Domain\Core\Entity\UserSearch;
use App\Infrastructure\Persistence\Doctrine\Repository\PlanRepository;
use App\Infrastructure\Persistence\UuidGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class UpgradeRequestInputDataTransformer implements DataTransformerInterface
{
    private $security;
    private $planRepository;

    public function __construct(Security $security, PlanRepository $planRepository)
    {
        $this->security = $security;
        $this->planRepository = $planRepository;
    }

    /**
     * @param UpgradeRequestInput $object
     * @param string $to
     * @param array $context
     * @return UserSearch|object
     */
    public function transform($object, string $to, array $context = [])
    {
        return new Order(UuidGenerator::generate(), $this->security->getUser(), $this->planRepository->findOneByName($object->planName));
    }

    public function supportsTransformation($object, string $to, array $context = []): bool
    {
        if ($object instanceof Order) {
            return false;
        }

        return Order::class === $to && null !== ($context['input']['class'] ?? null);
    }


}
