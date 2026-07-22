<?php

namespace App\Http\Controllers\Admin;

use App\Facades\AdminTheme as Theme;
use App\Http\Controllers\Controller;

class WebhooksController extends Controller
{
    public function index()
    {
        return Theme::view('webhooks');
    }
}
