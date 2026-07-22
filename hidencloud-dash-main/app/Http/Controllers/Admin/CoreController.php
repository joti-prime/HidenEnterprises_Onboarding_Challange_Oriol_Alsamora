<?php

namespace App\Http\Controllers\Admin;

use App\Facades\AdminTheme as Theme;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class CoreController extends Controller
{
    // return login page view
    public function setup()
    {
        return Theme::view('setup');
    }
}
