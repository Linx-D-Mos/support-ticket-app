<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StatsService;

class DashboardController extends Controller
{
    public function stats(StatsService $stats){
        $kpis = $stats->statsCreate();
        return response()->json($kpis);
    }
}
