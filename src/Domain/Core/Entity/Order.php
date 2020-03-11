<?php

declare(strict_types=1);

namespace App\Domain\Core\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Domain\Core\Dto\UpgradeRequestInput;
use App\Domain\Core\Dto\UpgradeRequestOutput;
use App\Domain\Core\Event\OrderEvent;
use App\Infrastructure\Persistence\Doctrine\DoctrineAware\UserAware;
use Doctrine\ORM\Mapping as ORM;
use Knp\Rad\DomainEvent\Provider;
use Knp\Rad\DomainEvent\ProviderTrait;
use Money\Money;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     cacheHeaders={"no-store"},
 *     input=UpgradeRequestInput::class,
 *     output=UpgradeRequestOutput::class,
 *     collectionOperations={
 *          "post",
 *          "get"
 *      },
 *     itemOperations={"get", "delete"},
 * )
 * @UserAware()
 * @ORM\Entity(repositoryClass="App\Infrastructure\Persistence\Doctrine\Repository\OrderRepository")
 * @ORM\Table(name="`order`")
 */
class Order implements Provider
{
    use ProviderTrait;

    /**
     * @Groups({"read"})
     * @ORM\Id()
     * @ORM\Column(type="string", length=255)
     */
    private $id;

    /**
     * @Groups({"read"})
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @Groups({"read"})
     * @var Plan
     * @Assert\Valid()
     * @ORM\ManyToOne(targetEntity="App\Domain\Core\Entity\Plan", inversedBy="orders")
     */
    private $plan;

    /**
     * @var Money
     *
     * @ORM\Embedded(class="Money\Money")
     */
    private $payment;

    /**
     * @Groups({"read"})
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $paymentDate;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Domain\Core\Entity\User", inversedBy="orders")
     */
    private $user;

    public function __construct(string $id, User $user, Plan $plan)
    {
        $this->id = $id;
        $this->createdAt = new \DateTime();
        $this->plan = $plan;
        $this->user = $user;
        $this->payment = Money::USD(0);

        // Only dev user can pay for a hidden plan
        if (!$plan->isVisible() && !$user->isDev()) {
            throw new \DomainException('Invalid plan user');
        }

        $this->events[] = new OrderEvent($this, OrderEvent::TYPE_PLACED);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function pay(Money $money): void
    {
        if ($money->lessThan($this->plan->getPrice())) {
            throw new \DomainException('Payment amount is less than plan price');
        }

        $this->paymentDate = new \DateTime();
        $this->payment = $money;

        $from = $this->paymentDate;
        $to = (clone $this->paymentDate)->modify('+30 days');
        $this->user->subscribe($this->plan, $from, $to);

        $this->events[] = new OrderEvent($this, OrderEvent::TYPE_PAID);
    }

    public function isPaid(): bool
    {
        return !!$this->paymentDate;
    }

    public function getPlan(): Plan
    {
        return $this->plan;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return Money
     */
    public function getPayment(): Money
    {
        return $this->payment;
    }

    /**
     * @return \DateTime|null
     */
    public function getPaymentDate(): ?\DateTime
    {
        return $this->paymentDate;
    }

}
