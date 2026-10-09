<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Enums\EventStatus;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Organization;
use App\Models\Promoter;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                'utilizadores' => User::count(),
                'promotores' => Promoter::count(),
                'organizacoes' => Organization::count(),
                'eventos_publicados' => Event::where('status', EventStatus::Published->value)->count(),
                'eventos_pendentes' => Event::where('status', EventStatus::Pending->value)->count(),
            ],
            'rolesBreakdown' => UserRoleAssignment::query()
                ->selectRaw('role, count(*) as total')
                ->groupBy('role')
                ->pluck('total', 'role'),
        ]);
    }
}
