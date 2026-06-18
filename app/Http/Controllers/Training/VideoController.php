<?php

namespace App\Http\Controllers\Training;

use App\Http\Controllers\Controller;
use App\Models\EndurQuizQuestion;
use App\Models\EndurQuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VideoController extends Controller
{
    public const MODULES = [
        [
            'id'          => 'introduction',
            'title'       => 'Introduction to OpenLink and Endur',
            'description' => 'Overview of the Endur platform, its managers, and how Front Office, Middle Office, and Back Office roles interact.',
            'slides'      => 18,
            'duration'    => '4:58',
            'icon'        => 'bi-play-circle',
        ],
        [
            'id'          => 'common-functionality',
            'title'       => 'Common System Functionality',
            'description' => 'Browser window navigation, query manager, table viewer configuration, searching, and data export to Excel.',
            'slides'      => 13,
            'duration'    => '3:32',
            'icon'        => 'bi-grid',
        ],
        [
            'id'          => 'system-setup',
            'title'       => 'System Setup: Admin and Reference Manager',
            'description' => 'System configuration, static data, security groups, party setup, portfolios, personnel, and party agreements.',
            'slides'      => 24,
            'duration'    => '8:20',
            'icon'        => 'bi-gear',
        ],
        [
            'id'          => 'market-and-trading',
            'title'       => 'Market Manager and Trading Concepts',
            'description' => 'Index and curve setup, trade numbering, trade lifecycle, power trading products, and portfolio revaluations.',
            'slides'      => 22,
            'duration'    => '7:34',
            'icon'        => 'bi-graph-up',
        ],
        [
            'id'          => 'deal-entry',
            'title'       => 'Deal Entry, Pricing and Validation',
            'description' => 'Primary and secondary input, deal pricing, NPV, validation, portfolio revaluations, and the full trade lifecycle.',
            'slides'      => 28,
            'duration'    => '11:10',
            'icon'        => 'bi-lightning',
        ],
    ];

    private function withAvailability(array $modules): array
    {
        return array_map(function ($m) {
            $m['available'] = file_exists(public_path("videos/{$m['id']}-voiced.mp4"));
            return $m;
        }, $modules);
    }

    public function index()
    {
        $userId  = Auth::id();
        $modules = $this->withAvailability(self::MODULES);

        // Attach best pass for each module
        foreach ($modules as &$m) {
            $m['passed'] = EndurQuizAttempt::where('user_id', $userId)
                ->where('module_id', $m['id'])
                ->where('passed', true)
                ->exists();
        }

        $totalDuration = '35:34';
        $totalSlides   = array_sum(array_column(self::MODULES, 'slides'));

        return view('training.videos.index', compact('modules', 'totalDuration', 'totalSlides'));
    }

    public function show(string $id)
    {
        $module = collect(self::MODULES)->firstWhere('id', $id);
        abort_unless($module, 404);

        $userId = Auth::id();
        $module['available'] = file_exists(public_path("videos/{$id}-voiced.mp4"));

        $allModules   = $this->withAvailability(self::MODULES);
        $currentIndex = collect($allModules)->search(fn($m) => $m['id'] === $id);
        $prev = $currentIndex > 0 ? $allModules[$currentIndex - 1] : null;
        $next = isset($allModules[$currentIndex + 1]) ? $allModules[$currentIndex + 1] : null;

        $questions   = EndurQuizQuestion::where('module_id', $id)->orderBy('sort_order')->get();
        $bestAttempt = EndurQuizAttempt::where('user_id', $userId)
                           ->where('module_id', $id)
                           ->orderByDesc('score')
                           ->first();

        return view('training.videos.show',
            compact('module', 'allModules', 'prev', 'next', 'questions', 'bestAttempt'));
    }

    public function submitQuiz(Request $request, string $id)
    {
        $module = collect(self::MODULES)->firstWhere('id', $id);
        abort_unless($module, 404);

        $questions = EndurQuizQuestion::where('module_id', $id)->orderBy('sort_order')->get();
        $answers   = $request->input('answers', []);
        $score     = 0;
        $results   = [];

        foreach ($questions as $q) {
            $chosen  = $answers[$q->id] ?? null;
            $correct = $chosen === $q->correct_option;
            if ($correct) $score++;
            $results[$q->id] = [
                'chosen'         => $chosen,
                'correct_option' => $q->correct_option,
                'correct'        => $correct,
            ];
        }

        $passed  = $score >= 9;
        $attempt = EndurQuizAttempt::create([
            'user_id'   => Auth::id(),
            'module_id' => $id,
            'score'     => $score,
            'passed'    => $passed,
            'answers'   => $results,
            'passed_at' => $passed ? now() : null,
        ]);

        return redirect()->route('training.videos.show', $id)
            ->with('quiz_result', [
                'score'      => $score,
                'total'      => $questions->count(),
                'passed'     => $passed,
                'attempt_id' => $attempt->id,
                'results'    => $results,
            ]);
    }
}
