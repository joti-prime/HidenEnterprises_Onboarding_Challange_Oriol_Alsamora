<?php

namespace App\Http\Controllers\Admin;

use App\Facades\AdminTheme as Theme;
use App\Http\Controllers\Controller;
use App\Models\Gateways\Gateway;
use Illuminate\Http\Request;

class GatewayController extends Controller
{
    public function index()
    {
        $gateways = Gateway::all();

        return Theme::view('gateways.index', compact('gateways'));
    }

    public function create()
    {
        $drivers = Gateway::drivers();
        foreach ($drivers as $key => $driver) {
            if (Gateway::where('driver', $driver['driver'])->exists()) {
                unset($drivers[$key]);
            }
        }

        return Theme::view('gateways.create', compact('drivers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'driver' => 'required|string|max:255',
        ]);
        $config = Gateway::drivers()[$request->driver];
        $data = [
            'name' => $request->name,
            'driver' => $request->driver,
            'type' => $config['type'] ?? 'once',
            'config' => [],
            'class' => $config['class'],
            'endpoint' => $config['endpoint'],
            'refund_support' => $config['refund_support'] ?? false,
            'blade_edit_path' => $config['blade_edit_path'] ?? null,
            'status' => 1,
        ];

        $conditions = ['driver' => $request->driver];
        Gateway::query()->updateOrCreate($conditions, $data);

        return redirect()->route('gateways.index')->with('success',
            trans('responses.gateway_save_success', ['default' => 'Gateway saved successfully.'])
        );
    }

    public function edit(Gateway $gateway)
    {
        return Theme::view('gateways.edit', compact('gateway'));
    }

    public function update(Request $request, Gateway $gateway)
    {
        Gateway::storeConfig($request, $gateway);

        return redirect()->route('gateways.index')->with('success',
            trans('responses.gateway_update_success', ['default' => 'Gateway updated successfully.'])
        );
    }

    public function toggle(Gateway $gateway)
    {
        $gateway->status = $gateway->status ? 0 : 1;
        $gateway->save();

        return redirect()->route('gateways.index')->with('success',
            trans('responses.gateway_update_success', ['default' => 'Gateway updated successfully.'])
        );
    }

    public function default(Gateway $gateway)
    {
        Gateway::query()->update(['default' => 0]);
        $gateway->default = 1;
        $gateway->save();

        return redirect()->route('gateways.index')->with('success',
            trans('responses.gateway_update_success', ['default' => 'Gateway updated successfully.'])
        );
    }

    public function destroy(Gateway $gateway)
    {
        $gateway->delete();

        return redirect()->route('gateways.index')->with('success',
            trans('responses.gateway_delete_success', ['default' => 'Gateway deleted successfully.'])
        );
    }
}
