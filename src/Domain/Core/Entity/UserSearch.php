<?php

namespace App\Domain\Core\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Domain\Core\Dto\UserSearchInput;
use App\Domain\Core\Event\UserEvent;
use App\Domain\Upwork\SearchUrlParser;
use App\Infrastructure\Persistence\Doctrine\DoctrineAware\UserAware;
use App\Ui\Validator\AddSearchUrl;
use Doctrine\ORM\Mapping as ORM;
use Knp\Rad\DomainEvent\Provider;
use Knp\Rad\DomainEvent\ProviderTrait;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     cacheHeaders={"no-store"},
 *     paginationEnabled=false,
 *     normalizationContext={"groups"={"read"}},
 *     input=UserSearchInput::class,
 *     itemOperations={"get", "delete"},
 *     collectionOperations={"get", "post"},
 * )
 * @UserAware()
 * @ORM\Entity()
 * @ORM\Table(name="user_search",uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique_user_link", columns={"user_id", "search_url"})
 * })
 */
class UserSearch implements Provider
{
    use ProviderTrait;

    /**
     * @Groups("read")
     * @ORM\Id()
     * @ORM\Column(type="string", length=255)
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Domain\Core\Entity\User", inversedBy="searches")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $user;

    /**
     * @Assert\NotBlank()
     * @Groups("read")
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $searchName;

    /**
     * @Groups("read")
     * @ORM\Column(type="array")
     */
    private $stopWords;

    /**
     * @AddSearchUrl()
     * @Assert\NotBlank()
     * @Groups({"read"})
     * @ORM\Column(type="string", length=500)
     */
    private $searchUrl;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPending;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    public function __construct(string $id, User $user, string $searchUrl, string $name = null, array $stopWords = [])
    {
        $this->id = $id;
        $this->user = $user;
        $this->searchUrl = $searchUrl;
        $this->searchName = null;
        $this->stopWords = [];
        $this->isPending = true;
        $this->createdAt = new \DateTime('now');

        if (null !== $name) {
            $this->searchName = $name;
            $this->completeCreation($user, $stopWords);
        }
    }

    public static function createFromUserSearchInput(string $id, User $user, UserSearchInput $input): self
    {
        return new static($id, $user, $input->getSearchUrl(), $input->getSearchName());
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getSearchName(): ?string
    {
        return $this->searchName;
    }

    public function getSearchUrl(): string
    {
        return $this->searchUrl;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function isPending(): bool
    {
        return $this->isPending;
    }

    public function getStopWords(): array
    {
        return $this->stopWords;
    }

    public function setName(string $name): void
    {
        $this->searchName = $name;
    }

    public function completeCreation(User $user, array $stopWords): void
    {
        $parser = new SearchUrlParser();
        if ($parser->isSimpleQuery($this->searchUrl)) {
            $this->searchUrl = $parser->convertSimpleQueryToUrl($this->searchUrl);
        }
        $this->stopWords = $stopWords;
        $this->isPending = false;
        $this->events[] = new UserEvent($user, $this, UserEvent::TYPE_SEARCH_ADDED);
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    public function __toString()
    {
        if ($this->searchName) {
            return $this->searchName;
        } else {
            return $this->id;
        }
    }

    /**
     * @param mixed $searchName
     */
    public function setSearchName($searchName): void
    {
        $this->searchName = $searchName;
    }

    /**
     * @param mixed $stopWords
     */
    public function setStopWords($stopWords): void
    {
        $this->stopWords = $stopWords;
    }

    /**
     * @param mixed $searchUrl
     */
    public function setSearchUrl($searchUrl): void
    {
        $this->searchUrl = $searchUrl;
    }

    /**
     * @param mixed $isPending
     */
    public function setIsPending($isPending): void
    {
        $this->isPending = $isPending;
    }


}
