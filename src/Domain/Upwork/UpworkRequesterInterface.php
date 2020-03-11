<?php

declare(strict_types=1);

namespace App\Domain\Upwork;

use App\Domain\Upwork\ValueObject\UpworkDataView;

interface UpworkRequesterInterface
{
    /**
     * @param mixed $filter
     * @return UpworkDataView[]
     */
    public function fetchUpdates($filter): array;
}
