<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StatsService;

class DashboardController extends Controller
{
    /**
     * Obtener estadísticas.
     *
     * @group Dashboard
     * @authenticated
     *
     * @response 200 {
     *    "total_tickets": 150,
     *    "open_tickets": 25,
     *    "closed_tickets": 100,
     *    "priority_distribution": {
     *        "high": 10,
     *        "medium": 50,
     *        "low": 90
     *    }
     * }
     */
    public function stats(StatsService $stats){
        $kpis = $stats->statsCreate();
        return response()->json($kpis);
    }
}
