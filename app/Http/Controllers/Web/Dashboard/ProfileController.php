<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Dashboard\ProfileRequest;
use App\Models\Organization;
use App\Models\Promoter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $entity = $this->entityFor($request);
        abort_if($entity === null, 404);

        return view('dashboard.profile', [
            'entity' => $entity,
            'type' => $entity instanceof Organization ? 'organização' : 'promotor',
        ]);
    }

    public function update(ProfileRequest $request): RedirectResponse
    {
        $entity = $this->entityFor($request);
        abort_if($entity === null, 404);

        $data = $request->safe()->only(['name', 'description', 'website', 'email', 'phone']);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        $entity->update($data);

        return back()->with('status', 'Perfil atualizado.');
    }

    private function entityFor(Request $request): Organization|Promoter|null
    {
        $user = $request->user();

        return $user->ownedOrganization ?? $user->promoterProfile;
    }
}
