<?php

namespace App\Http\Controllers\Admin;

use App\Facades\AdminTheme as Theme;
use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class ApiKeyController extends Controller
{
    /**
     * Display a list of all categories.
     *
     * @return Application|Factory|View
     */
    public function index()
    {
        $apiKeys = ApiKey::where('api_version', 'v1')->latest()->paginate(20);

        return Theme::view('api.v1.index', compact('apiKeys'));
    }

    /**
     * Show the form to create a new category.
     *
     * @return Application|Factory|View
     */
    public function create()
    {
        $routes = Route::getRoutes();
        $apiRoutes = [];

        foreach ($routes as $route) {
            if (in_array('application-api', $route->middleware())) {
                $apiRoutes[] = [
                    'identifier' => $route->getName(),
                    'uri' => $route->uri(),
                    'method' => $route->methods()[0],
                ];
            }
        }

        return Theme::view('api.v1.create', compact('apiRoutes'));
    }

    /**
     * Save the newly created category to the database.
     *
     * @return Application|Redirector|RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'ips' => 'array',
            'ips.*' => 'nullable|ip',
            'permissions' => 'array',
            'permissions.*' => 'string',
            'full_access' => 'required|boolean',
            'expires_at' => 'nullable|date|after:today',
        ]);

        if (!empty($validated['ips']) && !empty($validated['ips'][0])) {
            $allowed_ips = $validated['ips'];
        }

        $api_secret = Str::random(60);

        $api = new ApiKey();
        $api->user_id = auth()->user()->id;
        $api->api_version = 'v1';
        $api->secret = ApiKey::hash($api_secret);
        $api->description = $validated['description'];
        $api->allowed_ips = $allowed_ips ?? null;
        $api->full_permissions = $validated['full_access'];
        $api->permissions = $validated['permissions'] ?? [];
        $api->expires_at = $validated['expires_at'];
        $api->save();

        return redirect()->route('api-v1.index')->with('success', 'API Key created successfully: ' . $api_secret);
    }

    /**
     * @param  ApiKey  $api
     * @return RedirectResponse
     */
    public function show($apiKeyId)
    {
        $api = ApiKey::findOrFail($apiKeyId);
        $api_secret = Str::random(60);
        $api->secret = ApiKey::hash($api_secret);
        $api->save();

        return redirect()->route('api-v1.index')->with('success', 'API Key regenerated: ' . $api_secret);
    }

    /**
     * Delete the category from the database.
     *
     * @param  ApiKey  $api
     * @return RedirectResponse
     */
    public function destroy($apiKeyId)
    {
        $api = ApiKey::findOrFail($apiKeyId)->delete();

        return redirect()->route('api-v1.index')->with('success', 'API Key deleted successfully.');
    }
}
