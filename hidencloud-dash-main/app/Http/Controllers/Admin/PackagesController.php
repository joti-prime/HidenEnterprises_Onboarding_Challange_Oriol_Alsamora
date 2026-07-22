<?php

namespace App\Http\Controllers\Admin;

use App\Facades\AdminTheme as Theme;
use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackageConfigOption as ConfigOption;
use App\Models\PackageEmail;
use App\Models\PackageFeature;
use App\Models\PackagePrice;
use App\Models\PackageSettings;
use App\Models\PackageWebhook;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Str;

class PackagesController extends Controller
{
    /**
     * Display a list of all categories.
     *
     * @return Application|Factory|View
     */
    public function index()
    {
        $packages = Package::all();

        return Theme::view('packages.index', compact('packages'));
    }

    /**
     * Show the form to create a new package.
     *
     * @return Application|Factory|View
     */
    public function create()
    {
        return Theme::view('packages.create');
    }

    /**
     * Save the newly created package to the database.
     *
     * @return RedirectResponse|Redirector|Application
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|unique:packages,name|max:100',
            'service' => 'required|max:100',
            'category' => 'required|max:100',
            'status' => 'required|max:50',
        ]);

        // check if service provider is installed

        // create package
        $package = new Package;
        $package->name = $validatedData['name'];
        $package->service = $validatedData['service'];
        $package->category_id = $validatedData['category'];
        $package->status = $validatedData['status'];
        $package->save();

        // create initial price for package
        $price = new PackagePrice;
        $price->package_id = $package->id;
        $package->period = 30;
        $price->price = 5;
        $price->renewal_price = 5;
        $price->save();

        return redirect(route('packages.edit', ['package' => $package->id]))->with('success',
            trans('responses.package_create_success', ['name' => $package->name, 'default' => 'Package :name has been created'])
        );
    }

    public function updateServiceData(Request $request, Package $package)
    {
        $validated = $request->validate($package->service()->getPackageRules($package));

        // store the data
        $package->update(['data' => $request->except('_token')]);

        return redirect()->back()->with('success', __('responses.update_success', ['name' => 'package']));
    }

    public function clonePackage(Package $package)
    {
        // Clone the package
        $clonedPackage = $package->replicate();
        $clonedPackage->name = $clonedPackage->name . ' (Clone)';
        $clonedPackage->save(); // Save the cloned package

        // Now clone package prices
        foreach ($package->prices as $price) {
            $clonedPrice = $price->replicate();
            $clonedPrice->package_id = $clonedPackage->id;
            $clonedPrice->save(); // Save the cloned price
        }

        // Clone features
        foreach ($package->features as $feature) {
            $clonedFeature = $feature->replicate();
            $clonedFeature->package_id = $clonedPackage->id;
            $clonedFeature->save(); // Save the cloned feature
        }

        // clone package emails
        foreach ($package->emails as $email) {
            $clonedEmail = $email->replicate();
            $clonedEmail->package_id = $clonedPackage->id;
            $clonedEmail->save(); // Save the cloned email
        }

        // clone package emails
        foreach ($package->webhooks as $webhook) {
            $clonedWebhook = $webhook->replicate();
            $clonedWebhook->package_id = $clonedPackage->id;
            $clonedWebhook->save(); // Save the cloned price
        }

        return redirect(route('packages.edit', ['package' => $clonedPackage->id]))->with('success',
            trans('responses.package_clone_success', ['name' => $clonedPackage->name, 'default' => 'Package :name has been cloned'])
        );
    }

    /**
     * Show form to edit package.
     *
     * @param  int  $id
     * @return Application|Factory|View
     */
    public function edit(Package $package)
    {
        return Theme::view('packages.edit.index', compact('package'));
    }

    /**
     * Show form to features package.
     *
     * @param  int  $id
     * @return Application|Factory|View
     */
    public function editFeatures(Package $package)
    {
        return Theme::view('packages.edit.features', compact('package'));
    }

    /**
     * Show form to prices package.
     *
     * @param  int  $id
     * @return Application|Factory|View
     */
    public function editPrices(Package $package)
    {
        $prices = $package->prices->all();

        return Theme::view('packages.edit.prices', compact('package', 'prices'));
    }

    /**
     * Show form to service package.
     *
     * @param  int  $id
     * @return Application|Factory|View
     */
    public function editService(Package $package)
    {
        return Theme::view('packages.edit.service', compact('package'));
    }

    /**
     * Show config options for package.
     *
     * @param  int  $id
     * @return Application|Factory|View
     */
    public function configOptions(Package $package)
    {
        $configOptions = $package->configOptions->all();

        return Theme::view('packages.edit.config-options', compact('package', 'configOptions'));
    }

    /**
     * Add config option for package.
     *
     * @param  int  $id
     * @return RedirectResponse
     */
    public function addConfigOption(Package $package, Request $request)
    {
        $validatedData = $request->validate([
            'key' => 'required',
            'type' => 'required',
        ]);

        $rules = $package->service()->getPackageRule($package, $validatedData['key'], 'string') ?? 'required';

        // if key contains [] then remove it
        $validatedData['key'] = str_replace(['[', ']'], '', $validatedData['key']);

        $configOption = new ConfigOption;
        $configOption->package_id = $package->id;
        $configOption->key = $validatedData['key'];
        $configOption->type = $validatedData['type'];
        $configOption->rules = $rules;
        $configOption->save();

        return redirect()->back()->withSuccess('Config option added successfully');
    }

    /**
     * Update select config option for package.
     *
     * @param  int  $id
     * @return RedirectResponse
     */
    public function editConfigOption(Package $package, ConfigOption $option, Request $request)
    {
        return Theme::view('packages.edit.edit-config-options', compact('package', 'option'));
    }

    /**
     * Update select config option for package.
     *
     * @param  int  $id
     * @return RedirectResponse
     */
    public function updateConfigOption(Package $package, ConfigOption $option, Request $request)
    {
        $request->validate([
            'data.monthly_price_unit' => 'sometimes|numeric|min:0',
            'data.options.*.monthly_price' => 'sometimes|numeric|min:0',
        ]);

        $option->update([
            'data' => $request->get('data'),
            'rules' => $request->get('rules') ?? 'required',
        ]);

        return redirect()->back()->withSuccess('Config option updated successfully');
    }

    /**
     * Move config option up or down.
     *
     * @param  Request  $request
     * @param  int  $package_id
     * @return RedirectResponse
     */
    public function moveConfigOption(Package $package, ConfigOption $option)
    {
        $option->move(request()->get('direction'));

        return redirect()->back();
    }

    /**
     * Delete the config option from the database.
     *
     * @param  int  $id
     * @return RedirectResponse
     */
    public function deleteConfigOption(Package $package, ConfigOption $option)
    {
        $option->delete();

        return redirect()->back()->withSuccess('Config option deleted successfully');
    }

    /**
     * Show form to emails package.
     *
     * @param  int  $id
     * @return Application|Factory|View
     */
    public function editEmails(Package $package)
    {
        return Theme::view('packages.edit.emails', compact('package'));
    }

    /**
     * Show form to webhooks package.
     *
     * @param  int  $id
     * @return Application|Factory|View
     */
    public function editWebhooks(Package $package)
    {
        return Theme::view('packages.edit.webhooks', compact('package'));
    }

    /**
     * Show form to links package.
     *
     * @param  int  $id
     * @return Application|Factory|View
     */
    public function editLinks(Package $package)
    {
        return Theme::view('packages.edit.links', compact('package'));
    }

    /**
     * Update the category in the database.
     *
     * @param  int  $id
     * @return RedirectResponse
     */
    public function update(Request $request, Package $package)
    {
        $validatedData = $request->validate([
            'name' => 'required|max:100',
            'category' => 'required|max:100',
            'status' => 'required|max:100',
            'icon' => 'image|max:8048', // max 8MB file size
            'description' => 'nullable',
            'global_stock' => 'required',
            'client_stock' => 'required',
            'require_domain' => 'nullable|boolean',
            'settings' => 'nullable|array',
        ]);

        $package->name = $validatedData['name'];
        $package->category_id = $validatedData['category'];
        $package->status = $validatedData['status'];

        // store icon image
        if ($request->hasFile('icon')) {
            $file = $request->file('icon');
            $filename = Str::slug($validatedData['name'], '_') . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/products', $filename);
            $package->icon = $filename;
        }

        $package->description = $validatedData['description'] ?? '';
        $package->global_quantity = $validatedData['global_stock'];
        $package->client_quantity = $validatedData['client_stock'];
        $package->require_domain = (bool) $request->input('require_domain');
        $package->allow_notes = (bool) $request->input('allow_notes');
        $package->save();

        // store package settings
        foreach ($request->get('settings', []) as $key => $value) {
            PackageSettings::put($package->id, $key, $value);
        }

        return redirect()->back()->with('success',
            trans('responses.package_update_success', ['name' => $package->name, 'default' => 'Package :name has been updated'])
        );
    }

    public function updateService($id, $service)
    {
        $package = Package::query()->find($id);
        $package->service = $service;
        $package->save();

        return redirect()->back()->with('success',
            trans('responses.package_service_update_success',
                ['name' => $package->name, 'default' => 'Package :name service has been updated'])
        );
    }

    /**
     * Soft Delete the package from the database.
     *
     * @return RedirectResponse
     */
    public function destroy(int $id)
    {
        $package = Package::query()->find($id);

        if ($package->orders()->exists()) {
            return redirect()->back()->with('error',
                trans('responses.package_delete_error',
                    ['name' => $package->name, 'default' => 'The package :name must not have any orders attached to it to be deleted.'])
            );
        }

        $package->delete();

        return redirect()->back()->with('success', trans('responses.package_delete_success',
            ['name' => $package->name, 'default' => 'The package :name deleted success.']));
    }

    /**
     * Show form to create package price.
     *
     * @param  int  $package_id
     * @return RedirectResponse
     */
    public function createPrice(Request $request, Package $package)
    {
        $validatedData = $request->validate([
            'type' => 'required',
            'period' => 'numeric',
            'price' => 'required|numeric',
            'setup_fee' => 'required|numeric',
            'upgrade_fee' => 'required|numeric',
            'renewal_price' => 'numeric',
            'cancellation_fee' => 'numeric',
            'data' => 'nullable|json',
        ]);

        $price = new PackagePrice;
        $price->type = $validatedData['type'];
        $price->package_id = $package->id;
        $price->period = $validatedData['period'];
        $price->price = $validatedData['price'];
        $price->setup_fee = $validatedData['setup_fee'];
        $price->upgrade_fee = $validatedData['upgrade_fee'];
        $price->renewal_price = $validatedData['renewal_price'] ?? $validatedData['price'];
        $price->cancellation_fee = $validatedData['cancellation_fee'] ?? 0;
        $price->data = json_decode($request->input('data'));
        $price->save();

        return redirect()->back()->with('success',
            trans('responses.package_price_create_success', ['name' => $package->name, 'default' => 'Price for package :name successfully created'])
        );
    }

    /**
     * Show form to edit package price.
     *
     * @param  int  $id
     * @return RedirectResponse
     */
    public function updatePrice(Request $request, PackagePrice $price)
    {
        $validatedData = $request->validate([
            'type' => 'required',
            'period' => 'max:100',
            'price' => 'required|numeric',
            'setup_fee' => 'required|numeric',
            'upgrade_fee' => 'required|numeric',
            'renewal_price' => 'numeric',
            'cancellation_fee' => 'numeric',
            'data' => 'nullable|json',
            'is_active' => 'boolean',
        ]);

        $price->type = $validatedData['type'];
        $price->period = $validatedData['period'];
        $price->price = $validatedData['price'];
        $price->setup_fee = $validatedData['setup_fee'];
        $price->upgrade_fee = $validatedData['upgrade_fee'];
        $price->renewal_price = $validatedData['renewal_price'] ?? $validatedData['price'];
        $price->cancellation_fee = $validatedData['cancellation_fee'] ?? 0;
        $price->data = json_decode($request->input('data'));
        $price->is_active = $request->input('is_active', false);
        $price->save();

        return redirect()->back()->with('success',
            trans('responses.package_price_update_success', ['default' => 'The package price has been updated successfully'])
        );
    }

    /**
     * Delete the package price from the database.
     *
     * @param  int  $id
     * @return RedirectResponse
     */
    public function deletePrice(PackagePrice $price)
    {
        $price->delete();

        return redirect()->back()->with('success',
            trans('responses.package_price_delete_success', ['default' => 'The package price has been deleted successfully'])
        );
    }

    /**
     * Show form to create package feature.
     *
     * @param  int  $package_id
     * @return RedirectResponse
     */
    public function createFeature(Request $request, Package $package)
    {
        $validatedData = $request->validate([
            'icon' => 'required',
            'color' => 'required',
            'description' => 'required',
        ]);

        $feature = new PackageFeature;
        $feature->package_id = $package->id;
        $feature->order = 0;
        $feature->icon = $request->input('icon');
        $feature->color = $request->input('color');
        $feature->description = $request->input('description');
        $feature->save();

        return redirect()->back()->with('success', 'Successfully created package feature');
    }

    public function updateFeature(Request $request, Package $package, PackageFeature $feature)
    {
        $validatedData = $request->validate([
            'icon' => 'required',
            'color' => 'required',
            'description' => 'required',
        ]);

        $feature->icon = $request->input('icon');
        $feature->color = $request->input('color');
        $feature->description = $request->input('description');
        $feature->save();

        return redirect()->back()->with('success', 'Successfully updated package feature');
    }

    /**
     * Show form to create package feature.
     *
     * @param  Request  $request
     * @param  int  $package_id
     * @return RedirectResponse
     */
    public function moveFeature(Package $package, PackageFeature $feature, $direction)
    {
        $feature->$direction();

        return redirect()->back();
    }

    public function destroyFeature(Package $package, PackageFeature $feature)
    {
        $feature->delete();

        return redirect()->back()->with('success', 'Feature was deleted');
    }

    /**
     * Show form to create package email.
     *
     * @param  int  $package_id
     * @return RedirectResponse
     */
    public function createEmail(Request $request, Package $package)
    {
        $validatedData = $request->validate([
            'event' => 'required',
            'title' => 'required',
            'body' => 'required',
            'attachment' => 'nullable|file',
        ]);

        $email = new PackageEmail;
        $email->package_id = $package->id;
        $email->event = $request->input('event');
        $email->title = $request->input('title');
        $email->body = $request->input('body');

        // store file image
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $file->storeAs('attachments', $file->getClientOriginalName());
            $email->attachment = "attachments/{$file->getClientOriginalName()}";
        }

        $email->save();

        return redirect()->back()->with('success', 'Created event successfully');
    }

    /**
     * Show form to edit package email.
     *
     * @param  int  $id
     * @return RedirectResponse
     */
    public function updateEmail(Request $request, PackageEmail $email)
    {
        $validatedData = $request->validate([
            'event' => 'required',
            'title' => 'required',
            'body' => 'required',
            'attachment' => 'nullable|file',
        ]);

        $email->package_id = $email->package_id;
        $email->event = $request->input('event');
        $email->title = $request->input('title');
        $email->body = $request->input('body');

        // store file image
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $file->storeAs('attachments', $file->getClientOriginalName());
            $email->attachment = "attachments/{$file->getClientOriginalName()}";
        }

        $email->save();

        return redirect()->back()->with('success', 'Updated event successfully');
    }

    /**
     * Delete the package email from the database.
     *
     * @param  int  $id
     * @return RedirectResponse
     */
    public function deleteEmail(PackageEmail $email)
    {
        $email->delete();

        return redirect()->back()->with('success', 'The package event has been deleted');
    }

    /**
     * Show form to create package email.
     *
     * @param  int  $package_id
     * @return RedirectResponse
     */
    public function createWebhook(Request $request, Package $package)
    {
        $validatedData = $request->validate([
            'event' => 'required',
            'method' => 'required',
            'url' => 'required',
            'data' => 'nullable|json',
            'header' => 'nullable|json',
        ]);

        $webhook = new PackageWebhook;
        $webhook->package_id = $package->id;
        $webhook->event = $request->input('event');
        $webhook->method = $request->input('method');
        $webhook->url = $request->input('url');
        $webhook->data = json_decode($request->input('data'));
        $webhook->headers = json_decode($request->input('headers'));

        $webhook->save();

        return redirect()->back()->with('success', 'Created event successfully');
    }

    /**
     * Show form to edit package email.
     *
     * @param  int  $id
     * @return RedirectResponse
     */
    public function updateWebhook(Request $request, PackageWebhook $webhook)
    {
        $validatedData = $request->validate([
            'event' => 'required',
            'method' => 'required',
            'url' => 'required',
            'data' => 'nullable|json',
            'header' => 'nullable|json',
        ]);

        $webhook->package_id = $webhook->package_id;
        $webhook->event = $request->input('event');
        $webhook->method = $request->input('method');
        $webhook->url = $request->input('url');
        $webhook->data = json_decode($request->input('data'));
        $webhook->headers = json_decode($request->input('headers'));
        $webhook->save();

        return redirect()->back()->with('success', 'Updated event successfully');
    }

    /**
     * Delete the package email from the database.
     *
     * @param  int  $id
     * @return RedirectResponse
     */
    public function deleteWebhook(PackageWebhook $webhook)
    {
        $webhook->delete();

        return redirect()->back()->with('success', 'The package event has been deleted');
    }
}
