<?php

namespace App\Domain\Core\Entity;

use App\Domain\Core\Dto\NewUserDto;
use App\Domain\Core\Event\UserEvent;
use App\Domain\Core\Exception\SearchLimitReachedException;
use App\Domain\TelegramBot\ValueObject\TelegramUserStructure;
use App\Infrastructure\Form\Dto\UserPlanDto;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Knp\Rad\DomainEvent\Provider;
use Knp\Rad\DomainEvent\ProviderTrait;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Infrastructure\Persistence\Doctrine\Repository\UserRepository")
 * @UniqueEntity(fields={"username"}, message="There is already an account with this username")
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email")
 */
class User implements UserInterface, Provider
{
    use ProviderTrait;

    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=255)
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $telegramRef;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isEnabled;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $currentPlace;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $password;

    /**
     * @var Plan|null
     *
     * @ORM\ManyToOne(targetEntity="Plan")
     */
    private $currentPlan;

    /**
     * @var \DateTimeInterface|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $currentPlanFrom;

    /**
     * @var \DateTimeInterface|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $currentPlanTo;

    /**
     * @var UserSearch[]|ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="App\Domain\Core\Entity\UserSearch",
     *     mappedBy="user",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     */
    private $searches;

    /**
     * @var Order[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Domain\Core\Entity\Order", mappedBy="user", cascade={"persist", "remove"})
     */
    private $orders;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $isDev;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $githubClientID;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $facebookClientID;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $googleClientID;

    public function __construct(string $id, string $username, string $telegramName = null)
    {
        $this->id = $id;
        $this->name = $telegramName;
        $this->username = $username;
        $this->isEnabled = true;
        $this->createdAt = new \DateTime('now');
        $this->searches = new ArrayCollection();
        $this->isDev = false;
        $this->orders = new ArrayCollection();
    }

    public static function createFromTelegram(
        string $id,
        TelegramUserStructure $telegramUser
    ): self {
        $user = new self($id, (string)$telegramUser, $telegramUser->getUsername());
        $user->telegramRef = $telegramUser->getId();

        return $user;
    }

    public static function createFromNewUserDto(string $id, NewUserDto $dto): self
    {
        $user = new self($id, $dto->getEmail());
        $user->email = $dto->getEmail();

        return $user;
    }

    public static function createFromOauthProvider(string $id, string $username, ?string $email = null): self
    {
        $user = new self($id, $email ? $email : $username);
        if ($email) {
            $user->email = $email;
        }

        return $user;
    }

    public static function createFromGithubOauth(string $id, string $username, ?string $email = null): self
    {
        $user = new self($id, $email ? $email : $username);

        return $user;
    }

    public function markAsDev()
    {
        $this->isDev = true;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTelegramRef(): ?int
    {
        return $this->telegramRef;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getCurrentPlace(): ?string
    {
        return $this->currentPlace;
    }

    public function setCurrentPlace(string $currentPlace): void
    {
        $this->currentPlace = $currentPlace;
    }

    public function resetPlace(): void
    {
        $this->currentPlace = null;
    }

    /**
     * @return Collection|UserSearch[]
     */
    public function getSearches(): Collection
    {
        return $this->searches;
    }

    public function getPendingSearch(): ?UserSearch
    {
        $res = $this->searches->filter(function (UserSearch $search) {
            return $search->isPending();
        })->first() ?: null;

        return $res;
    }

    public function updatePlanFromDto(UserPlanDto $dto)
    {
        $this->currentPlan = $dto->plan;
        $this->currentPlanFrom = $dto->from;
        $this->currentPlanTo = $dto->to;
    }

    public function addSearchPending(string $id, string $searchUrl): void
    {
        $search = new UserSearch($id, $this, $searchUrl);
        $this->searches[] = $search;
    }

    public function removeSearch(UserSearch $search): void
    {
        $this->searches->removeElement($search);

        $this->events[] = new UserEvent($this, $search, UserEvent::TYPE_SEARCH_REMOVED);
    }

    public function isReachedSearchLimit(): bool
    {
        $limit = $this->currentPlan ? $this->currentPlan->getSearchCount() : 1;

        return $this->searches->count() >= $limit;
    }

    public function assertCanAddSearch(): void
    {
        if ($this->isReachedSearchLimit()) {
            $searchCount = $this->getSearches()->count();
            $text = 'You can have only '.$searchCount.' search link(s). Please upgrade your Plan to add more.';
            throw new SearchLimitReachedException($text);
        }
    }

    public function hasSearch(string $search): bool
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('searchName', $search));

        return !$this->searches->matching($criteria)->isEmpty();
    }

    /**
     * @return Order[]|ArrayCollection
     */
    public function getOrders()
    {
        return $this->orders;
    }

    public function subscribe(Plan $plan, \DateTimeInterface $from, \DateTimeInterface $to): void
    {
        $this->currentPlan = $plan;
        $this->currentPlanFrom = $from;
        $this->currentPlanTo = $to;
    }

    public function getCurrentPlan(): ?Plan
    {
        return $this->currentPlan;
    }

    public function getCurrentPlanFrom(): ?\DateTimeInterface
    {
        return $this->currentPlanFrom;
    }

    public function getCurrentPlanTo(): ?\DateTimeInterface
    {
        return $this->currentPlanTo;
    }

    public function isDev(): bool
    {
        return $this->isDev;
    }

    public function hashPassword(UserPasswordEncoderInterface $encoder, string $pass)
    {
        $this->password = $encoder->encodePassword($this, $pass);
    }

    public function getRoles(): array
    {
        $roles = ['ROLE_USER'];
        if ($this->isDev) {
            $roles[] = 'ROLE_ADMIN';
        }

        return $roles;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getSalt()
    {
        return null;
    }

    public function eraseCredentials()
    {
    }

    public function isPremiumUser(): bool
    {
        if ($this->currentPlan instanceof Plan) {
            return $this->currentPlan->isPremium();
        } else {
            return false;
        }
    }

    public function isFreePlanUser(): bool
    {
        if ($this->currentPlan instanceof Plan) {
            return !$this->currentPlan->isPremium();
        } else {
            return false;
        }
    }

    public function connectGithubAuthWithExists(string $id)
    {
        $this->githubClientID = $id;
    }

    public function connectFacebookAuthWithExists(string $id)
    {
        $this->githubClientID = $id;
    }

    public function connectGoogleAuthWithExists(string $id)
    {
        $this->githubClientID = $id;
    }

    public function __toString()
    {
        return $this->username;
    }
}
