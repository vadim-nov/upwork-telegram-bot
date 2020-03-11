<?php
/**
 * Created by PhpStorm.
 * User: macbook
 * Date: 09/04/2019
 * Time: 08:39
 */

namespace App\Domain\Upwork\ValueObject;


class UpworkSearchFilter
{
    private $originalSearch;
    private $query;
    private $category;
    private $subcategory;
    private $clientLocation;
    private $jobType;
    private $experienceLevel;
    private $clientHistory;
    private $clientInfo;
    private $numberOfProposals;
    private $budget;
    private $hoursPerWeek;
    private $projectLength;
    private $sort;
    private $skill;

    public function __construct(string $originalSearch)
    {
        $this->originalSearch = $originalSearch;
    }

    /**
     * @return string | null
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param string | null $query
     */
    public function setQuery(?string $query): void
    {
        if (!empty($query)) {
            $this->query = $query;
        }
    }

    /**
     * @return string | null
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param string | null $category
     */
    public function setCategory(?string $category): void
    {
        if (!empty($category)) {
            $this->category = $category;
        }
    }

    /**
     * @param string | null $subcategory
     */
    public function setSubCategory(?string $subcategory): void
    {
        if (!empty($subcategory)) {
            $this->subcategory = $subcategory;
        }
    }

    /**
     * @return string | null
     */
    public function getSubCategory()
    {
        return $this->subcategory;
    }

    /**
     * @return string | null
     */
    public function getClientLocation()
    {
        return $this->clientLocation;
    }

    /**
     * @param string | null $clientLocation
     */
    public function setClientLocation(?string $clientLocation): void
    {
        if (!empty($clientLocation)) {
            $this->clientLocation = $clientLocation;
        }
    }

    /**
     * @return string | null
     */
    public function getJobType()
    {
        return $this->jobType;
    }

    /**
     * @param string | null $jobType
     */
    public function setJobType(?string $jobType): void
    {
        if (!empty($jobType)) {
            $this->jobType = $jobType;
        }
    }

    /**
     * @return string | null
     */
    public function getExperienceLevel()
    {
        return $this->experienceLevel;
    }

    /**
     * @param string | null $experienceLevel
     */
    public function setExperienceLevel(?string $experienceLevel): void
    {
        if (!empty($experienceLevel)) {
            $this->experienceLevel = $experienceLevel;
        }
    }

    /**
     * @return string | null
     */
    public function getClientHistory()
    {
        return $this->clientHistory;
    }

    /**
     * @param string | null $clientHistory
     */
    public function setClientHistory(?string $clientHistory): void
    {
        if (!empty($clientHistory)) {
            $this->clientHistory = $clientHistory;
        }
    }

    /**
     * @return string | null
     */
    public function getClientInfo()
    {
        return $this->clientInfo;
    }

    /**
     * @param string | null $clientInfo
     */
    public function setClientInfo(?string $clientInfo): void
    {
        if (!empty($clientInfo)) {
            $this->clientInfo = $clientInfo;
        }
    }

    /**
     * @return string | null
     */
    public function getNumberOfProposals()
    {
        return $this->numberOfProposals;
    }

    /**
     * @param string | null $numberOfProposals
     */
    public function setNumberOfProposals(?string $numberOfProposals): void
    {
        if (!empty($numberOfProposals)) {
            $this->numberOfProposals = $numberOfProposals;
        }
    }

    /**
     * @return string | null
     */
    public function getBudget()
    {
        return $this->budget;
    }

    /**
     * @param string | null $budget
     */
    public function setBudget(?string $budget): void
    {
        if (!empty($budget)) {
            $this->budget = $budget;
        }
    }

    /**
     * @return string | null
     */
    public function getHoursPerWeek()
    {
        return $this->hoursPerWeek;
    }

    /**
     * @param string | null $hoursPerWeek
     */
    public function setHoursPerWeek(?string $hoursPerWeek): void
    {
        if (!empty($hoursPerWeek)) {
            $this->hoursPerWeek = $hoursPerWeek;
        }
    }

    /**
     * @return string | null
     */
    public function getProjectLength()
    {
        return $this->projectLength;
    }

    /**
     * @param string | null $projectLength
     */
    public function setProjectLength(?string $projectLength): void
    {
        if (!empty($projectLength)) {
            $this->projectLength = $projectLength;
        }
    }

    /**
     * @return mixed
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * @param mixed $sort
     */
    public function setSort($sort): void
    {
        $this->sort = $sort;
    }

    /**
     * @return mixed
     */
    public function getSkill()
    {
        return $this->skill;
    }

    /**
     * @param mixed $skill
     */
    public function setSkill($skill): void
    {
        $this->skill = $skill;
    }

    /**
     * @return string
     */
    public function getOriginalSearch(): string
    {
        return $this->originalSearch;
    }

    public function getRssLink(): string
    {
        $query = '';
        $query .= $this->query ? '&q='.$this->query : null;
        $query .= $this->category ? '&category2='.$this->category : null;
        $query .= $this->subcategory ? '&subcategory2='.$this->subcategory : null;
        $query .= $this->jobType ? '&job_type='.$this->jobType : null;
        $query .= $this->budget ? '&budget='.$this->budget : null;
        $query .= $this->experienceLevel ? '&contractor_tier='.$this->experienceLevel : null;
        $query .= $this->clientLocation ? '&location='.$this->clientLocation : null;
        $query .= $this->clientInfo ? '&'.$this->clientInfo : null;
        $query .= $this->clientHistory ? '&client_hires='.$this->clientHistory : null;
        $query .= $this->numberOfProposals ? '&proposals='.$this->numberOfProposals : null;
        $query .= $this->hoursPerWeek ? '&workload='.$this->hoursPerWeek : null;
        $query .= $this->projectLength ? '&duration_v3='.$this->projectLength : null;
        $query .= $this->skill ? '&skills_filter='.$this->skill : null;

        return 'https://www.upwork.com/ab/feed/jobs/rss?'.$query.'&paging=0%3B50&api_params=1&securityToken=8f694b7779b56b483de7ab1f14eeb71a77db4bb46cce100d8e22d3a6969b5e063b4f01c98cada6c262db262ef6395fe3abe51baecee8d4e9940b5b23852bfaad&userUid=1004454894076182528&orgUid=1004454894105542657';
    }
}
