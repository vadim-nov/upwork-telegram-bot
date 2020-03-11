<?php

declare(strict_types=1);

namespace App\Domain\Core\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Domain\Core\Dto\PlanOutput;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Money\Money;

/**
 * @ApiResource(
 *     collectionOperations={"get"},
 *     output=PlanOutput::class,
 *     itemOperations={"get"},
 *     )
 * @ORM\Entity(repositoryClass="App\Infrastructure\Persistence\Doctrine\Repository\PlanRepository")
 */
class Plan
{
    const PLAN_STANDARD = 'Standard';
    const PLAN_PREMIUM = 'Premium';
    /**
     * @var string
     *
     * @ORM\Id()
     * @ORM\Column(type="string", length=255)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @var Money
     *
     * @ORM\Embedded(class="Money\Money")
     */
    private $price;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $searchCount;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $updateFrequency;

    /**
     * @var Order[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Order", mappedBy="plan")
     */
    private $orders;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default":1})
     */
    private $isVisible;

    public function __construct(
        string $id,
        string $name,
        Money $price,
        int $searchCount,
        int $updateFrequency,
        bool $isVisible = true
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
        $this->searchCount = $searchCount;
        $this->updateFrequency = $updateFrequency;
        $this->isVisible = $isVisible;
        $this->orders = new ArrayCollection();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isPremium(): bool
    {
        return $this->price->getAmount() > 0;
    }

    public function getPrice(): Money
    {
        return $this->price;
    }

    public function getSearchCount(): int
    {
        return $this->searchCount;
    }

    public function getUpdateFrequency(): int
    {
        return $this->updateFrequency;
    }

    public function isVisible(): bool
    {
        return $this->isVisible;
    }

    public function __toString()
    {
        return $this->name;
    }
}
