<?php

namespace App\Domain\Upwork\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Domain\Core\Entity\User;
use App\Domain\Core\Entity\UserSearch;
use App\Domain\Upwork\ValueObject\UpworkDataView;
use App\Infrastructure\Delivery\Web\MarkAllJobsAsRead;
use App\Infrastructure\Persistence\Doctrine\DoctrineAware\UserAware;
use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @UserAware()
 * @ApiResource(
 *     normalizationContext={"groups"={"read"}},
 *     denormalizationContext={"groups"={"write"}},
 *     attributes={"order"={"createdAt": "DESC"}},
 *     collectionOperations={
 *      "get"
 *     },
 *     itemOperations={
 *      "get",
 *      "put"
 *     }
 * )
 * @ApiFilter(DateFilter::class, properties={"pubDate"})
 * @ApiFilter(BooleanFilter::class, properties={"isRead"})
 * @ApiFilter(SearchFilter::class, properties={"userSearch": "exact"})
 * @ApiFilter(OrderFilter::class, properties={"pubDate": "DESC", "isRead": "DESC"})
 * @ORM\Entity(repositoryClass="App\Infrastructure\Persistence\Doctrine\Repository\UpworkJobRepository")
 */
class UpworkJob
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=255)
     * @Groups("read")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $upworkRef;

    /**
     * @var UserSearch
     * @ORM\ManyToOne(targetEntity="App\Domain\Core\Entity\UserSearch", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $userSearch;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Domain\Core\Entity\User", cascade={"persist"})
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("read")
     */
    private $link;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @Groups("read")
     * @ORM\Column(type="datetime")
     */
    private $pubDate;

    /**
     * @ORM\Column(type="datetime")
     * @Groups("read")
     */
    private $createdAt;

    /**
     * @Groups({"read", "write"})
     * @ORM\Column(type="boolean")
     */
    private $isRead;
    /**
     * @Groups({"write"})
     * @ORM\Column(type="boolean")
     */
    private $isRemoved;


    public function __construct(
        string $id,
        UserSearch $userSearch,
        string $upworkRef,
        string $link,
        string $title,
        string $description,
        \DateTimeInterface $pubDate
    ) {
        $this->id = $id;
        $this->userSearch = $userSearch;
        $this->user = $userSearch->getUser();
        $this->upworkRef = $upworkRef;
        $this->link = $link;
        $this->title = $title;
        $this->description = $description;
        $this->pubDate = $pubDate;
        $this->isRead = false;
        $this->isRemoved = false;
        $this->createdAt = Carbon::now()->toDateTime();
    }

    public static function fromUpworkDataView(string $id, UserSearch $userSearch, UpworkDataView $dataView): self
    {
        $self = new self($id,
            $userSearch,
            $dataView->getLink(),
            $dataView->getLink(),
            $dataView->getTitle(),
            $dataView->getDescription(),
            $dataView->getPubDate()
        );

        return $self;
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUpworkRef(): string
    {
        return $this->upworkRef;
    }

    /**
     * @return string
     */
    public function getLink(): string
    {
        return $this->link;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @Groups("read")
     * @return string
     */
    public function getCleanedTitle(): string
    {
        return html_entity_decode(str_replace(' - Upwork', '', $this->title));
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @Groups("read")
     * @return string
     */
    public function getCleanedDescription(): string
    {
        $res = html_entity_decode(str_replace(PHP_EOL, '<br />', $this->description));
        $res = str_replace("<br /><br /><br />", "<br />", $res);
        $res = str_replace("<br /><br />", "<br />", $res);
        return $res;
    }


    public function getPubDate(): \DateTimeInterface
    {
        return $this->pubDate;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUserSearch(): UserSearch
    {
        return $this->userSearch;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getIsRead(): bool
    {
        return $this->isRead;
    }


    public function setIsRead(bool $isRead): self
    {
        $this->isRead = $isRead;

        return $this;
    }

    public function setIsRemoved(bool $isRemoved): self
    {
        $this->isRemoved = $isRemoved;

        return $this;
    }
}
