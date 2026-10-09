<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Admin\VenueRequest;
use App\Models\Venue;
use App\Support\Slug;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VenueController extends Controller
{
    public function index(): View
    {
        return view('admin.venues.index', [
            'venues' => Venue::query()->orderBy('name')->paginate(50),
        ]);
    }

    public function create(): View
    {
        return view('admin.venues.form', ['venue' => new Venue(['is_active' => true])]);
    }

    public function store(VenueRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = Slug::unique(Venue::class, $data['name']);
        $data['is_active'] = $request->boolean('is_active');
        Venue::create($data);

        return redirect()->route('admin.venues.index')->with('status', 'Local criado.');
    }

    public function edit(Venue $venue): View
    {
        return view('admin.venues.form', ['venue' => $venue]);
    }

    public function update(VenueRequest $request, Venue $venue): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        $venue->update($data);

        return redirect()->route('admin.venues.index')->with('status', 'Local atualizado.');
    }

    public function destroy(Venue $venue): RedirectResponse
    {
        $venue->delete();

        return redirect()->route('admin.venues.index')->with('status', 'Local removido.');
    }
}
