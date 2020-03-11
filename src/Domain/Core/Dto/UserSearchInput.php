<?php


namespace App\Domain\Core\Dto;


class UserSearchInput
{
    private $searchUrl;
    private $searchName;

    /**
     * @return mixed
     */
    public function getSearchUrl()
    {
        return $this->searchUrl;
    }

    /**
     * @param mixed $searchUrl
     */
    public function setSearchUrl($searchUrl): void
    {
        $this->searchUrl = trim($searchUrl);
    }

    /**
     * @return mixed
     */
    public function getSearchName()
    {
        return $this->searchName;
    }

    /**
     * @param mixed $searchName
     */
    public function setSearchName($searchName): void
    {
        $this->searchName = trim($searchName);
    }
}
