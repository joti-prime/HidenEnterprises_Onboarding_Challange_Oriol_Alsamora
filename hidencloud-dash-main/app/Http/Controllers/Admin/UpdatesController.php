<?php

namespace App\Http\Controllers\Admin;

use App\Facades\AdminTheme as Theme;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class UpdatesController extends Controller
{
    protected string $version_api = 'https://wemx.net/api/wemx/versions';

    public function index()
    {
        $response = Http::acceptJson()->get($this->version_api);
        if ($response->failed()) {
            return redirect('/admin')->withError(__('admin.failed_connect_remove_server_please_try_again'));
        }

        $versions = $response->object();
        $latest_version = $versions[0];

        return Theme::view('updates', compact('versions', 'latest_version'));
    }

    public function install($version, $type = 'stable')
    {
        if (!Cache::has('queue_active')) {
            return redirect()->back()->with('Automated updates require the queue to be active. Please enable the queue and try again.');
        }

        $license = settings('encrypted::license_key');
        Artisan::queue('wemx:update', ['license_key' => $license, '--type' => $type, '--ver' => $version]);

        Cache::put('app_updating', [
            'updating' => true,
            'version' => $version,
            'type' => $type,
            'progress' => __('admin.preparing_for_installation'),
        ], 120);

        return redirect()->route('updates.index')->with(['success' => __('admin.installation_started_please_wait')]);
    }

    public function trackProgress()
    {
        return Cache::get('app_updating', ['updating' => false]);
    }

    public function cancelUpdate()
    {
        return Cache::forget('app_updating');
    }
}
