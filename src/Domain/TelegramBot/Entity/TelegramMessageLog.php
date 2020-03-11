<?php
/**
 * Created by PhpStorm.
 * User: macbook
 * Date: 14/05/2019
 * Time: 12:50
 */

namespace App\Domain\TelegramBot\Entity;

use App\Domain\Core\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use App\Infrastructure\Persistence\Doctrine\Repository\TelegramMessageLogRepository;
/**
* @ORM\Entity(repositoryClass=TelegramMessageLogRepository::class)
* @ORM\Table(name="`telegram_message_log`")
*/
class TelegramMessageLog
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Domain\Core\Entity\User")
     */
    private $user;

    /**
     * @ORM\Column(type="text")
     */
    private $text;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isInbound;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    public function __construct(User $user, string $text, bool $isInbound, \DateTime $createdAt = null)
    {
        $this->user = $user;
        $this->text = $text;
        $this->isInbound = $isInbound;
        $this->createdAt = $createdAt ?? new \DateTime('now');
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @return mixed
     */
    public function getIsInbound()
    {
        return $this->isInbound;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}
