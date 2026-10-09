<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\My;

use App\Http\Controllers\Controller;
use App\Services\Recommendations\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecommendationController extends Controller
{
    public function __construct(private readonly RecommendationService $recommendations) {}

    public function index(Request $request): View
    {
        return view('my.recommendations', [
            'events' => $this->recommendations->for($request->user()),
        ]);
    }
}
