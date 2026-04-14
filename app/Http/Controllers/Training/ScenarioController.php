<?php

namespace App\Http\Controllers\Training;

use App\Http\Controllers\Controller;
use App\Models\GuidedScenario;
use Illuminate\View\View;

class ScenarioController extends Controller
{
    public function index(): View
    {
        $scenarios = GuidedScenario::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->groupBy('module');

        return view('training.scenarios.index', compact('scenarios'));
    }

    public function show(GuidedScenario $scenario): View
    {
        return view('training.scenarios.show', compact('scenario'));
    }
}
