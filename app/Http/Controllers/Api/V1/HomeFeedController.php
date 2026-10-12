<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Services\Events\HomeFeedService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomeFeedController extends Controller
{
    public function __construct(private readonly HomeFeedService $feed) {}

    public function index(Request $request): JsonResponse
    {
        $sections = array_map(fn (array $section): array => [
            'key' => $section['key'],
            'title' => $section['title'],
            'events' => EventResource::collection($section['events']),
        ], $this->feed->for($request->user()));

        return response()->json(['data' => $sections]);
    }
}
