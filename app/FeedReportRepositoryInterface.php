<?php

namespace App;

use App\DTO\FeedReportDTO;
use App\Models\MovieFeedReport;

interface FeedReportRepositoryInterface
{
    public function findReport(int $startIndex, int $maxResults): ?MovieFeedReport;

    public function saveReport(FeedReportDTO $report): MovieFeedReport;
}
