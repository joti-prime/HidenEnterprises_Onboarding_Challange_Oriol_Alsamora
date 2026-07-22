<?php

namespace App\Http\Controllers\Client;

use App\Facades\Theme;
use App\Http\Controllers\Controller;

class DownloadsController extends Controller
{
    public function index()
    {
        return Theme::view('downloads');
    }
}
