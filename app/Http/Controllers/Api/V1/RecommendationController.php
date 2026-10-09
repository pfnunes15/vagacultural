<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Services\Recommendations\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RecommendationController extends Controller
{
    public function __construct(private readonly RecommendationService $recommendations) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return EventResource::collection($this->recommendations->for($request->user()));
    }
}
