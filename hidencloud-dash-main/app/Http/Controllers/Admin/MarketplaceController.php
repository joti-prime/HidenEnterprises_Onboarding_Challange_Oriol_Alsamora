<?php

namespace App\Http\Controllers\Admin;

use App\Entities\ResourceApiClient;
use App\Facades\AdminTheme as Theme;
use App\Http\Controllers\Controller;

class MarketplaceController extends Controller
{
    public function index()
    {
        $categories = $this->api()->categories()['data'];
        $resources = $this->api()->getAllResources(
            category: request()->get('category', null),
            page: request()->get('page', 1),
            sort: 'rating'
        );

        return Theme::view('marketplace.index', compact('resources', 'categories'));
    }

    public function view($resource_id)
    {
        $resource = $this->api()->getResource($resource_id)['data'];

        return Theme::view('marketplace.view', compact('resource'));
    }

    protected function api()
    {
        return new ResourceApiClient;
    }
}
