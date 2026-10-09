<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Admin\OrganizationRequest;
use App\Models\Organization;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Support\Slug;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OrganizationController extends Controller
{
    public function index(): View
    {
        return view('admin.organizations.index', [
            'organizations' => Organization::query()->with('owner')->withCount('promoters')->orderBy('name')->paginate(50),
        ]);
    }

    public function create(): View
    {
        return view('admin.organizations.form', [
            'organization' => new Organization(['is_active' => true]),
            'users' => User::query()->orderBy('first_name')->get(),
        ]);
    }

    public function store(OrganizationRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = Slug::unique(Organization::class, $data['name']);
        $data['is_active'] = $request->boolean('is_active');
        $organization = Organization::create($data);

        $this->grantOwnerRole($organization);

        return redirect()->route('admin.organizations.index')->with('status', 'Organização criada.');
    }

    public function edit(Organization $organization): View
    {
        return view('admin.organizations.form', [
            'organization' => $organization,
            'users' => User::query()->orderBy('first_name')->get(),
        ]);
    }

    public function update(OrganizationRequest $request, Organization $organization): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        $organization->update($data);

        $this->grantOwnerRole($organization);

        return redirect()->route('admin.organizations.index')->with('status', 'Organização atualizada.');
    }

    public function destroy(Organization $organization): RedirectResponse
    {
        $organization->delete();

        return redirect()->route('admin.organizations.index')->with('status', 'Organização removida.');
    }

    /** Ensure the assigned owner account carries the organization role. */
    private function grantOwnerRole(Organization $organization): void
    {
        if ($organization->user_id === null) {
            return;
        }

        UserRoleAssignment::firstOrCreate([
            'user_id' => $organization->user_id,
            'role' => UserRole::Organization->value,
        ]);
    }
}
