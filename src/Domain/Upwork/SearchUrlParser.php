<?php
/**
 * Created by PhpStorm.
 * User: macbook
 * Date: 30/04/2019
 * Time: 12:27
 */

namespace App\Domain\Upwork;


use App\Domain\Upwork\Exception\InvalidUpworkUrlException;
use App\Domain\Upwork\ValueObject\UpworkSearchFilter;

class SearchUrlParser
{
    private function cleanSearchUrl(string $searchUrl): string {
        return str_replace('/ab/jobs/search/', '/search/jobs/', $searchUrl);
    }
    public function parse(string $searchUrl): UpworkSearchFilter
    {
        $searchUrl = $this->cleanSearchUrl($searchUrl);
        $this->assertValidSearchUrl($searchUrl);
        preg_match('/https:\/\/www.upwork.com\/search\/jobs\/(.*)/', $searchUrl, $searchMatches);
        $query = $searchMatches[1] ?? '';

        $searchFilter = new UpworkSearchFilter($searchUrl);
        if ($query) {
            $searchFilter->setQuery($this->parseQuery($query));
            $searchFilter->setCategory($this->parseCategory($query));
            $searchFilter->setSubCategory($this->parseSubCategory($query));
            $searchFilter->setJobType($this->parseJobType($query));
            $searchFilter->setBudget($this->parseBudget($query));
            $searchFilter->setExperienceLevel($this->parseExperienceLevel($query));
            $searchFilter->setClientLocation($this->parseClientLocation($query));
            $searchFilter->setClientHistory($this->parseClientHistory($query));
            $searchFilter->setClientInfo($this->parseClientInfo($query));
            $searchFilter->setNumberOfProposals($this->parseNumberOfProposals($query));
            $searchFilter->setHoursPerWeek($this->parseHoursPerWeek($query));
            $searchFilter->setProjectLength($this->parseProjectLength($query));
            $searchFilter->setSort($this->parseSort($query));
            $searchFilter->setSkill($this->parseSkill($query));
        } elseif ($this->isSimpleQuery($searchUrl)) {
            $searchFilter->setQuery($searchUrl);
        }

        return $searchFilter;
    }

    public function isSimpleQuery(string $searchUrl): bool
    {
        return $searchUrl !== '' && strstr($searchUrl, 'https://') === false AND strstr($searchUrl,
                'upwork.com') === false;
    }

    public function convertSimpleQueryToUrl(string $searchUrl): string
    {
        return 'https://www.upwork.com/search/jobs/?q='.$searchUrl;
    }

    /** @throws InvalidUpworkUrlException */
    public function assertValidSearchUrl(string $searchUrl): void
    {
        $searchUrl = $this->cleanSearchUrl($searchUrl);
        if (!$this->isSimpleQuery($searchUrl)) {
            if ($searchUrl === 'https://www.upwork.com/search/jobs') {
                return;
            }
            $searchMatches = null;
            preg_match('/https:\/\/www.upwork.com\/search\/jobs\/(.*)/', $searchUrl, $searchMatches);
            if (empty($searchMatches[1])) {
                throw new InvalidUpworkUrlException('Invalid Upwork search link. Try again please.');
            }
        }
    }

    private function parseQuery(string $query): ?string
    {
        preg_match('/.*?[&\?]q=(.*?)(&|$)/', $query, $matches);
        if (!empty($matches[1])) {
            return $matches[1];
        }

        return null;
    }

    private function parseCategory(string $query): ?string
    {
        preg_match('/c\/(.*?)\//', $query, $queryMatches);
        if (!empty($queryMatches[1])) {
            $subcategory = str_replace('-', '_', $queryMatches[1]);

            return $subcategory;
        } else {
            preg_match('/.*?[&\?]c=(.*?)(&|$)/', $query, $queryMatches);
            if (!empty($queryMatches[1])) {
                $subcategory = str_replace('-', '_', $queryMatches[1]);

                return $subcategory;
            }
        }

        return null;
    }

    private function parseSubCategory(string $query): ?string
    {
        preg_match('/sc\/(.*?)\//', $query, $queryMatches);
        if (!empty($queryMatches[1])) {
            $subcategory = str_replace('-', '_', $queryMatches[1]);

            return $subcategory;
        } else {
            preg_match('/.*?[&\?]sc=(.*?)(&|$)/', $query, $queryMatches);
            if (!empty($queryMatches[1])) {
                $subcategory = str_replace('-', '_', $queryMatches[1]);

                return $subcategory;
            }
        }

        return null;
    }

    private function parseJobType(string $query): ?string
    {
        if (false !== strpos($query, 't/1/?')) {
            return 'fixed';
        } elseif (false !== strpos($query, 't/0/?')) {
            return 'hourly';
        }

        return null;
    }

    private function parseBudget(string $query): ?string
    {
        preg_match('/.*?[&\?]amount=(.*?)(&|$)/', $query, $matches);
        if (!empty($matches[1])) {
            return $matches[1];
        }

        return null;
    }

    private function parseExperienceLevel(string $query): ?string
    {
        preg_match('/.*?[&\?]contractor_tier=(.*?)(&|$)/', $query, $matches);
        if (!empty($matches[1])) {
            return $matches[1];
        }

        return null;
    }


    private function parseClientLocation(string $query): ?string
    {
        preg_match('/.*?[&\?]location=(.*?)(&|$)/', $query, $matches);
        if (!empty($matches[1])) {
            return $matches[1];
        }

        return null;
    }

    private function parseClientHistory(string $query): ?string
    {
        preg_match('/.*?[&\?]client_hires=(.*?)(&|$)/', $query, $matches);
        if (!empty($matches[1])) {
            return $matches[1];
        }

        return null;
    }

    private function parseClientInfo(string $query): ?string
    {
        preg_match('/.*?[&\?](payment_verified=1)(&|$)/', $query, $matches);
        if (!empty($matches[1])) {
            return 'verified_payment_only=1';
        }

        return null;
    }

    private function parseNumberOfProposals(string $query): ?string
    {
        preg_match('/.*?[&\?]proposals=(.*?)(&|$)/', $query, $matches);
        if (!empty($matches[1])) {
            return $matches[1];
        }

        return null;
    }

    private function parseHoursPerWeek(string $query): ?string
    {
        preg_match('/.*?[&\?]workload=(.*?)(&|$)/', $query, $matches);
        if (!empty($matches[1])) {
            return $matches[1] === 'as_needed' ? 'as_needed,part_time' : $matches[1];
        }

        return null;
    }

    private function parseProjectLength(string $query): ?string
    {
        preg_match('/.*?[&\?]duration_v3=(.*?)(&|$)/', $query, $matches);
        if (!empty($matches[1])) {
            $result = str_replace('weeks', 'week', $matches[1]);
            $result = str_replace('months', 'month', $result);

            return $result;
        }

        return null;
    }

    private function parseSort(string $query): ?string
    {
        preg_match('/.*?[&\?]sort=(.*?)(&|$)/', $query, $matches);
        if (!empty($matches[1])) {

            return $matches[1];
        }

        return null;
    }

    private function parseSkill(string $query): ?string
    {
        preg_match('/skill\/(.*?)\//', $query, $matches);
        if (!empty($matches[1])) {
            return $matches[1];
        }

        return null;
    }
}
