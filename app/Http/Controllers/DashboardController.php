<?php

namespace App\Http\Controllers;

use App\Models\Dataset;
use App\Models\Import;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
        // Terapkan middleware auth untuk semua method
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();

        // Get recently accessed datasets (owned + shared, ordered by last update)
        $recentDatasets = Dataset::where(function($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->orWhereHas('shares', function($subQuery) use ($user) {
                      $subQuery->where('user_id', $user->id);
                  });
        })
        ->with(['user', 'shares' => function($query) use ($user) {
            $query->where('user_id', $user->id);
        }])
        ->orderBy('updated_at', 'desc')
        ->limit(5)
        ->get();

        $stats = [
            'total_datasets' => Dataset::where('user_id', $user->id)->count(),
            'total_imports' => Import::where('user_id', $user->id)->count(),
            'recent_datasets' => $recentDatasets
        ];

        return view('dashboard.index', compact('stats'));
    }

    public function analytics()
    {
        $user = auth()->user();
        
        $datasetStats = Dataset::where('user_id', $user->id)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $importStats = Import::where('user_id', $user->id)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        return view('dashboard.analytics', compact('datasetStats', 'importStats'));
    }

    public function datasetAnalytics()
    {
        $user = auth()->user();
        
        $datasetStats = Dataset::where('user_id', $user->id)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get();
        
        return response()->json($datasetStats);
    }

    public function importAnalytics()
    {
        $user = auth()->user();
        
        $importStats = Import::where('user_id', $user->id)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();
        
        return response()->json($importStats);
    }

    public function ocrAnalytics()
    {
        $user = auth()->user();
        
        $ocrStats = OcrResult::where('user_id', $user->id)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();
        
        return response()->json($ocrStats);
    }
}