<?php

namespace App\Http\Controllers\Client;

use App\Facades\Theme;
use App\Http\Controllers\Controller;
use App\Models\Settings;

class MaintenanceController extends Controller
{
    public function activation()
    {
        // Check if user is authenticated and has active status
        if (auth()->check() && auth()->user()->status === 'active') {
            return redirect()->route('dashboard');
        }

        return Theme::view('restricted', [
            'title' => 'Access Restricted',
            'desc' => Settings::get('registration_activation_message',
                'Your account has been placed in a queue and requires manual approval by an administrator.'),
            'color' => 'warning',
            'icon' => '<i class=\'bx bx-user\' ></i>']);
    }

    public function maintenance()
    {
        // Check if maintenance mode is disabled
        if (Settings::get('maintenance', 'false') == 'false') {
            return redirect()->route('dashboard');
        }

        return Theme::view('restricted', ['title' => 'Maintenance',
            'desc' => Settings::get('maintenance_message'),
            'color' => 'warning',
            'icon' => '<i class=\'bx bx-hard-hat\' ></i>']);
    }
}