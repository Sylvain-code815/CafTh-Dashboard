<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Stats;

class DashboardController extends Controller
{
    public function index(): void
    {
        $stats = new Stats();

        $this->render('dashboard/index', [
            'title'      => 'Tableau de bord',
            'revenue'    => $stats->revenue(),
            'summary'    => $stats->salesSummary(),
            'top'        => $stats->topProducts(),
            'categories' => $stats->salesByCategory(),
            'newClients' => $stats->newClientsByMonth(),
        ]);
    }
}