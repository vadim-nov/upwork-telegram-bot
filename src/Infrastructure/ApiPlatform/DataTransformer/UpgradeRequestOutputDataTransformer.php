<?php


namespace App\Infrastructure\ApiPlatform\DataTransformer;


use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use App\Domain\Core\Dto\UpgradeRequestOutput;
use App\Domain\Core\Entity\Order;
use App\Domain\Payment\PaymentGatewayInterface;
use App\Infrastructure\Persistence\Doctrine\Repository\PlanRepository;
use Money\Formatter\IntlMoneyFormatter;
use Symfony\Component\Security\Core\Security;

class UpgradeRequestOutputDataTransformer implements DataTransformerInterface
{
    private $security;
    private $planRepository;
    private $paymentGateway;
    private $moneyFormatter;

    public function __construct(
        Security $security,
        PlanRepository $planRepository,
        PaymentGatewayInterface $paymentGateway,
        IntlMoneyFormatter $moneyFormatter
    ) {
        $this->security = $security;
        $this->planRepository = $planRepository;
        $this->paymentGateway = $paymentGateway;
        $this->moneyFormatter = $moneyFormatter;
    }

    /**
     * @param Order $object
     * @param string $to
     * @param array $context
     * @return UpgradeRequestOutput
     */
    public function transform($object, string $to, array $context = [])
    {
        $url = $this->paymentGateway->generatePaymentPageUrl($object->getId(), $object->getPlan()->getPrice());

        return new UpgradeRequestOutput(
            $object->getId(),
            $object->getPlan()->getName(),
            $this->moneyFormatter->format($object->getPlan()->getPrice()),
            $url,
            $object->isPaid()
        );
    }

    public function supportsTransformation($object, string $to, array $context = []): bool
    {
        return UpgradeRequestOutput::class === $to && $object instanceof Order;
    }


}
