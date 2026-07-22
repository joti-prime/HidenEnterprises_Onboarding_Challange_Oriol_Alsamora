<?php

namespace App\Http\Controllers\Admin;

use App\Facades\AdminTheme as Theme;
use App\Http\Controllers\Controller;
use App\Models\ErrorLog;

class LogsController extends Controller
{
    public function index()
    {
        $logs = ErrorLog::query()->latest()->where('severity', request()->input('severity', 'CRITICAL'))->paginate(25);

        return Theme::view('logs', compact('logs'));
    }
}
