<?php

namespace App\Services\UptimeRobot\Http\Controllers;

use App\Models\Order;
use App\Services\UptimeRobot\Entities\UptimeRobotApi;
use App\Http\Controllers\Controller;

class UptimeRobotController extends Controller
{
    /**
     * Pause the monitor directly from the client "Manage" page.
     */
    public function pauseMonitor(Order $order)
    {
        try {
            (new UptimeRobotApi())->pauseMonitor($order->external_id);

            return redirect()->back()->with('success', 'Monitor paused.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Could not pause the monitor: ' . $e->getMessage());
        }
    }

    /**
     * Resume the monitor directly from the client "Manage" page.
     */
    public function resumeMonitor(Order $order)
    {
        try {
            (new UptimeRobotApi())->resumeMonitor($order->external_id);

            return redirect()->back()->with('success', 'Monitor resumed.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Could not resume the monitor: ' . $e->getMessage());
        }
    }

    /**
     * Edit the monitor's friendly name, URL and check interval.
     */
    public function updateMonitor(Order $order)
    {
        $validated = request()->validate([
            'friendlyName' => ['required', 'string', 'max:100'],
            'url' => ['required', 'url'],
            'interval' => ['required', 'integer', 'min:60'],
        ]);

        try {
            (new UptimeRobotApi())->updateMonitor($order->external_id, $validated);

            $order->update([
                'options' => array_merge($order->options ?? [], [
                    'monitor_name' => $validated['friendlyName'],
                    'monitor_url' => $validated['url'],
                ]),
            ]);

            return redirect()->back()->with('success', 'Monitor updated.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Could not update the monitor: ' . $e->getMessage());
        }
    }
}
