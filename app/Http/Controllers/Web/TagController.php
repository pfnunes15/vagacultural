<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
    /**
     * Existing tags matching a term (popularity first), to suggest while typing
     * and avoid redundant/insignificant tags.
     */
    public function suggest(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q', ''));

        $tags = Tag::query()
            ->when($term !== '', fn ($q) => $q->where('name', 'like', '%' . $term . '%'))
            ->withCount('events')
            ->orderByDesc('events_count')
            ->orderBy('name')
            ->limit(10)
            ->pluck('name');

        return response()->json(['data' => $tags]);
    }
}
