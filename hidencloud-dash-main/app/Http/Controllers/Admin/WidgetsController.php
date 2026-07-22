<?php

namespace App\Http\Controllers\Admin;

use App\Entities\ResourceApiClient;
use App\Facades\AdminTheme as Theme;
use App\Facades\Service;
use App\Http\Controllers\Controller;
use App\Models\Settings;
use Illuminate\Http\Request;

class WidgetsController extends Controller
{
    public function index()
    {
        return Theme::view('widgets.index');
    }
}
