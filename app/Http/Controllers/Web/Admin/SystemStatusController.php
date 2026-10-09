<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Services\System\SystemStatusService;
use Illuminate\View\View;

class SystemStatusController extends Controller
{
    public function index(SystemStatusService $status): View
    {
        return view('admin.system.status', ['checks' => $status->checks()]);
    }
}
