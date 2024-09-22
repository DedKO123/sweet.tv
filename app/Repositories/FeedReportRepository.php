<?php

namespace App\Repositories;

use App\DTO\FeedReportDTO;
use App\FeedReportRepositoryInterface;
use App\Models\MovieFeedReport;

class FeedReportRepository implements FeedReportRepositoryInterface
{
    public function findReport(int $startIndex, int $maxResults): ?MovieFeedReport
    {
        return MovieFeedReport::query()
            ->where('start_index', $startIndex)
            ->where('max_results', $maxResults)
            ->first();
    }

    public function saveReport(FeedReportDTO $reportDTO): MovieFeedReport
    {
       return  MovieFeedReport::query()->create(
          $reportDTO->toArray()
        );
    }
}
