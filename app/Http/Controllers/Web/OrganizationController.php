<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Services\Events\PublicEventService;
use Illuminate\View\View;

class OrganizationController extends Controller
{
    public function __construct(private readonly PublicEventService $events) {}

    public function index(): View
    {
        return view('organizations.index', [
            'organizations' => Organization::query()->where('is_active', true)->orderBy('name')->paginate(24),
        ]);
    }

    public function show(Organization $organization): View
    {
        abort_unless($organization->is_active, 404);

        return view('organizations.show', [
            'organization' => $organization->loadCount('promoters'),
            'events' => $this->events->forOrganization($organization),
        ]);
    }
}
