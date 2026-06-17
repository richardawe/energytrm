<?php

namespace App\Http\Controllers\Training;

use App\Http\Controllers\Controller;

class VideoController extends Controller
{
    public const MODULES = [
        [
            'id'          => 'introduction',
            'title'       => 'Introduction to OpenLink & Endur',
            'description' => 'Overview of the Endur platform, its managers, and how Front Office, Middle Office, and Back Office roles interact.',
            'slides'      => 18,
            'duration'    => '2:24',
            'icon'        => 'bi-play-circle',
        ],
        [
            'id'          => 'common-functionality',
            'title'       => 'Common System Functionality',
            'description' => 'Browser window navigation, query manager, table viewer configuration, searching, and data export to Excel.',
            'slides'      => 13,
            'duration'    => '1:44',
            'icon'        => 'bi-grid',
        ],
        [
            'id'          => 'admin-manager',
            'title'       => 'Admin Manager',
            'description' => 'System configuration, compliance tools, activity audit, error log, data model extension, and transport network.',
            'slides'      => 8,
            'duration'    => '1:04',
            'icon'        => 'bi-gear',
        ],
        [
            'id'          => 'reference-manager',
            'title'       => 'Reference Manager',
            'description' => 'Managing portfolios, personnel, party groups, legal entities, business units, and party agreements.',
            'slides'      => 11,
            'duration'    => '1:28',
            'icon'        => 'bi-people',
        ],
        [
            'id'          => 'market-manager',
            'title'       => 'Market Manager',
            'description' => 'Defining indexes, price curves, volatilities, correlations, and viewing/saving market prices.',
            'slides'      => 7,
            'duration'    => '0:56',
            'icon'        => 'bi-graph-up',
        ],
        [
            'id'          => 'trading-manager',
            'title'       => 'Trading Manager & Trade Lifecycle',
            'description' => 'Deal entry, trade numbering, power trading products, validation, portfolio revaluations, and the full trade lifecycle.',
            'slides'      => 28,
            'duration'    => '3:44',
            'icon'        => 'bi-lightning',
        ],
    ];

    public function index()
    {
        $modules = array_map(function ($m) {
            $m['available'] = file_exists(public_path("videos/{$m['id']}.mp4"));
            return $m;
        }, self::MODULES);

        $totalDuration = '11:20';
        $totalSlides   = array_sum(array_column(self::MODULES, 'slides'));

        return view('training.videos.index', compact('modules', 'totalDuration', 'totalSlides'));
    }

    public function show(string $id)
    {
        $module = collect(self::MODULES)->firstWhere('id', $id);
        abort_unless($module, 404);

        $module['available'] = file_exists(public_path("videos/{$id}.mp4"));

        $allModules = array_map(function ($m) {
            $m['available'] = file_exists(public_path("videos/{$m['id']}.mp4"));
            return $m;
        }, self::MODULES);

        $currentIndex = collect($allModules)->search(fn($m) => $m['id'] === $id);
        $prev = $currentIndex > 0 ? $allModules[$currentIndex - 1] : null;
        $next = isset($allModules[$currentIndex + 1]) ? $allModules[$currentIndex + 1] : null;

        return view('training.videos.show', compact('module', 'allModules', 'prev', 'next'));
    }
}
