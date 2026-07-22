<?php

namespace Modules\StatusMonitor\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\StatusMonitor\Entities\Monitor;
use Modules\StatusMonitor\Entities\MonitorChecker;

class MonitorController extends Controller
{
    public function index(): View
    {
        $monitors = Monitor::orderBy('name')->get();

        return view('statusmonitor::admin.index', compact('monitors'));
    }

    public function create(): View
    {
        return view('statusmonitor::admin.create');
    }

    public function edit(Monitor $monitor): View
    {
        return view('statusmonitor::admin.edit', compact('monitor'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        Monitor::create($data);

        return redirect()
            ->route('admin.statusmonitor.index')
            ->with('success', 'Monitor created successfully.');
    }

    public function update(Request $request, Monitor $monitor): RedirectResponse
    {
        $data = $this->validated($request);

        $monitor->update($data);

        return redirect()
            ->route('admin.statusmonitor.index')
            ->with('success', 'Monitor updated successfully.');
    }

    public function toggle(Monitor $monitor): RedirectResponse
    {
        $monitor->update(['is_enabled' => !$monitor->is_enabled]);

        return redirect()
            ->route('admin.statusmonitor.index')
            ->with('success', $monitor->is_enabled ? 'Monitor enabled.' : 'Monitor disabled.');
    }

    public function destroy(Monitor $monitor): RedirectResponse
    {
        $monitor->delete();

        return redirect()
            ->route('admin.statusmonitor.index')
            ->with('success', 'Monitor deleted.');
    }

    public function checkNow(Monitor $monitor, MonitorChecker $checker): RedirectResponse
    {
        $checker->check($monitor);

        return redirect()
            ->route('admin.statusmonitor.index')
            ->with('success', "Checked \"{$monitor->name}\": now {$monitor->last_status}.");
    }

    protected function validated(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'check_type' => ['required', 'in:http,tcp'],
            'target' => ['required', 'string', 'max:255'],
            'port' => ['nullable', 'integer', 'min:1', 'max:65535', 'required_if:check_type,tcp'],
            'expected_status_code' => ['nullable', 'integer', 'min:100', 'max:599'],
            'is_enabled' => ['sometimes', 'boolean'],
        ]);

        $validated['expected_status_code'] = $validated['expected_status_code'] ?? 200;
        $validated['is_enabled'] = $request->boolean('is_enabled', true);

        return $validated;
    }
}
