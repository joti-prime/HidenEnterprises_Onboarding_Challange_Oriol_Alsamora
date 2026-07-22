<?php

namespace Modules\StatusMonitor\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Modules\StatusMonitor\Entities\Monitor;

class StatusController extends Controller
{
    /**
     * Full status page: every monitor, green when up, red when down.
     */
    public function index(): View
    {
        $monitors = Monitor::where('is_enabled', true)->orderBy('name')->get();

        return view('statusmonitor::status', compact('monitors'));
    }
}
