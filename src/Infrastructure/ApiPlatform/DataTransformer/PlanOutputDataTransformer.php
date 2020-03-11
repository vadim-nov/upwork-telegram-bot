<?php


namespace App\Infrastructure\ApiPlatform\DataTransformer;


use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use App\Domain\Core\Dto\PlanOutput;
use App\Domain\Core\Entity\Plan;
use App\Domain\Core\Entity\User;
use Money\Formatter\IntlMoneyFormatter;
use Symfony\Component\Security\Core\Security;

class PlanOutputDataTransformer implements DataTransformerInterface
{
    private $security;
    private $moneyFormatter;

    public function __construct(
        Security $security,
        IntlMoneyFormatter $moneyFormatter
    ) {
        $this->security = $security;
        $this->moneyFormatter = $moneyFormatter;
    }

    /**
     * @param Plan $object
     * @param string $to
     * @param array $context
     * @return PlanOutput
     */
    public function transform($object, string $to, array $context = [])
    {
        $isCurrent = false;
        /** @var User $user */
        $user = $this->security->getUser();
        if ($plan = $user->getCurrentPlan()) {
            if ($plan->getName() === $object->getName()) {
                $isCurrent = true;
            }
        }

        return new PlanOutput(
            $object->getName(),
            $this->moneyFormatter->format($object->getPrice()),
            $object->getSearchCount(),
            $object->getUpdateFrequency(),
            $isCurrent
        );
    }

    public function supportsTransformation($object, string $to, array $context = []): bool
    {
        return PlanOutput::class === $to && $object instanceof Plan;
    }


}
