<?php
/**
 * Created by PhpStorm.
 * User: macbook
 * Date: 17/05/2019
 * Time: 10:47
 */

namespace App\Domain\Upwork;

use App\Domain\Core\Entity\UserSearch;
use App\Domain\Upwork\ValueObject\UpworkDataView;

class FilterJobUpdatesFromStopWords
{
    /**
     * @param UpworkDataView[] $upworkJobs
     * @return UpworkDataView[]
     */
    public function __invoke(UserSearch $userSearch, array $upworkJobs): array
    {
        $stopWords = $userSearch->getStopWords();
        if (!empty($stopWords)) {
            $filteredJobs = [];
            foreach ($upworkJobs as $job) {
                $jobTitle = mb_strtolower($job->getTitle());
                $jobDescription = mb_strtolower($job->getDescription());
                $jobLink = mb_strtolower($job->getLink());
                foreach ($stopWords as $stopWord) {
                    $stopWord = mb_strtolower($stopWord);
                    if (
                        false === mb_strpos($jobTitle, $stopWord)
                        && false === mb_strpos($jobDescription, $stopWord)
                        && false === mb_strpos($jobLink, $stopWord)
                    ) {
                        $filteredJobs[] = $job;
                    } else {
                        break;
                    }
                }
            }
        }

        return $filteredJobs ?? $upworkJobs;
    }
}