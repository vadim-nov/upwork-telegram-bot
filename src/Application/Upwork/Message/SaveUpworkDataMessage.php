<?php
/**
 * Created by PhpStorm.
 * User: macbook
 * Date: 10/04/2019
 * Time: 13:40
 */

namespace App\Application\Upwork\Message;


use App\Domain\Core\Entity\UserSearch;
use App\Domain\Upwork\ValueObject\UpworkDataView;

class SaveUpworkDataMessage
{
    private $upworkDataView;
    private $userSearch;

    public function __construct(UserSearch $userSearch, UpworkDataView $upworkDataView)
    {
        $this->upworkDataView = $upworkDataView;
        $this->userSearch = $userSearch;
    }

    /**
     * @return UpworkDataView
     */
    public function getUpworkDataView(): UpworkDataView
    {
        return $this->upworkDataView;
    }

    /**
     * @return UserSearch
     */
    public function getUserSearch(): UserSearch
    {
        return $this->userSearch;
    }

}
