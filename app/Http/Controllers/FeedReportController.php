<?php

namespace App\Http\Controllers;

use App\Services\FeedReportService;
use Illuminate\Http\Request;


class FeedReportController extends Controller
{
    public function __construct(public FeedReportService $feedReportService)
    {
    }

    public function index()
    {
        return view('index');

    }

    public function generateReport(Request $request)
    {
        try {
            $report = $this->feedReportService->findReport($request->start_index, $request->max_results);

            if ($report) {
                return response()->json([
                    'success' => true,
                    'report' => $report,
                    'message' => __('messages.report_generated')
                ]);
            }

            $reportData = $this->feedReportService->generateReport($request->start_index, $request->max_results);

            return response()->json([
                'success' => true,
                'report' => $reportData['report'],
                'message' => $reportData['message']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

}
