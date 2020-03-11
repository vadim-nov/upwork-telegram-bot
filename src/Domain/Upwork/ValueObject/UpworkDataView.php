<?php
/**
 * Created by PhpStorm.
 * User: macbook
 * Date: 09/04/2019
 * Time: 08:39
 */

namespace App\Domain\Upwork\ValueObject;


class UpworkDataView
{
    private $id;
    private $title;
    private $link;
    private $description;
    private $pubDate;

    public function __construct(string $id, string $link, string $title, string $description, \DateTimeInterface $pubDate)
    {
        $this->id = $id;
        $this->title = $title;
        $this->link = $link;
        $this->description = $description;
        $this->pubDate = $pubDate;
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
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }


    public function getPubDate(): \DateTimeInterface
    {
        return $this->pubDate;
    }
}
