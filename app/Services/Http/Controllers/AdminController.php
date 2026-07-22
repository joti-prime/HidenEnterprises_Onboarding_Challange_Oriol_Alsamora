<?php

namespace App\Services\CloudFlare\Http\Controllers;

use App\Facades\AdminTheme;
use App\Facades\Theme;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Package;
use App\Services\CloudFlare\Entities\CfService;
use App\Services\CloudFlare\Entities\PterodactylOrderDomains;
use App\Services\CloudFlare\Entities\CfHelper;

class AdminController extends Controller
{
    // AdminController for the CloudFlare service Pterodactyl
    public function pterodactyl()
    {
        $packages = Package::whereService('pterodactyl')->get();
        $items = CfService::whereIn('package_id', $packages->pluck('id'))->get();
        $domains = CfHelper::getDomainsList();
        return view(AdminTheme::serviceView('cloudflare', 'pterodactyl'), compact('packages', 'items', 'domains'));
    }

    public function pteroStore()
    {
        $this->validate(request(), [
            'package_id' => 'required|exists:packages,id',
            'zones_ids' => 'required|array',
        ]);

        CfService::create(request()->except('_token'));
        return redirect()->route('admin.cf.pterodactyl');
    }

    public function pteroUpdate(CfService $cfService)
    {
        $this->validate(request(), [
            'id' => 'required|exists:cf_services,id',
            'zones_ids' => 'required|array',
        ]);

        $cfService->update(request()->except('_token', 'id'));
        return redirect()->route('admin.cf.pterodactyl');
    }

    public function pteroDestroy(CfService $cfService)
    {
        $cfService->delete();
        return redirect()->back()->with('success', 'Service has been deleted');
    }

    // ClientController for the Pterodactyl service
    public function saveOrderDomain($service = 'pterodactyl')
    {
        $data = $this->validate(request(), [
            'order_id' => 'required|exists:orders,id',
            'subdomain' => 'required|string',
            'domain' => 'required|string',
            'ip' => 'required|ip',
            'port' => 'required|string',
        ]);
        $domain_data = explode('::', $data['domain']);
        $order = Order::find($data['order_id']);
        if ($order->package->service == $service && cf()::where('package_id', $order->package->id)->exists()) {
            $package_setting = cf()::where('package_id', $order->package->id)->first();
            $zones_ids = $package_setting->zones_ids;
            $type = $package_setting->type;
            if (in_array($domain_data[0], $zones_ids)) {
                if ($service !== 'pterodactyl') {
                    return redirect()->back()->with('error', 'Service not found');
                }
                $domain = PterodactylOrderDomains::where('order_id', $data['order_id'])->first() ?? new PterodactylOrderDomains();
    
                if ($domain->domain_data) {
                    CfHelper::deleteSrvDNSRecords($domain);
                }
    
                if (CfHelper::srvExist($data['subdomain'], $domain_data[1])) {
                    $domain->delete();
                    return redirect()->back()->with('error', 'Record already exists');
                }
    
                $domain->order_id = $data['order_id'];
                $domain->domain_data = [
                    'id' => $domain_data[0],
                    'domain' => $domain_data[1],
                    'subdomain' => $data['subdomain'],
                    'service' => CfHelper::getTypeData($type)['service'],
                    'proto' => CfHelper::getTypeData($type)['proto'],
                    'ip' => $data['ip'],
                    'port' => $data['port'],
                ];
                $domain->save();
                CfHelper::createSrvDNSRecords($domain);
            }
        } else {
            return redirect()->back()->with('error', 'Service not found');
        }
        return redirect()->back()->with('success', 'Domain has been saved');
    }

    // ClientController for the CloudFlare service
    public function editOrderDomain(Order $order)
    {
        if (auth()->user()->id != $order->user_id) {
            return redirect()->back()->with('error', __('responses.no_permission'));
        }
        return view(Theme::serviceView('cloudflare', 'edit'), compact('order'));
    }

    public function updateOrderDomain(Order $order)
    {
        if (auth()->user()->id != $order->user_id) {
            return redirect()->back()->with('error', __('responses.no_permission'));
        }
        $data = $this->validate(request(), [
            'subdomain' => 'required|string',
            'ip' => 'required|ip',
            'domain' => 'required|string',
            'port' => 'required|string',
        ]);
    
        $domainList = CfHelper::getDomainsList()->toArray();
        $currentSubdomain = $order->options['subdomain'];
        $currentDomain = $domainList[$order->options['domains']];
        foreach (CfHelper::dns()->listRecords($order->options['domains'])->result as $record) {
            if ($record->name == $currentSubdomain . '.' . $currentDomain) {
                CfHelper::dns()->deleteRecord($order->options['domains'], $record->id);
            }
        }
        
        if (CfHelper::srvExist($data['subdomain'], $domainList[$order->options['domains']])) {
            $order->options = [
                'subdomain' => '',
                'ip' => '',
                'domains' => $data['domain'],
                'port' => '',
            ];
            $order->save();
            return redirect()->back()->with('error', 'Subdomain already exists');
        }
        
        $order->options = [
            'subdomain' => $data['subdomain'],
            'ip' => $data['ip'],
            'domains' => $data['domain'],
            'port' => $data['port'],
        ];
        $order->save();
        
        CfHelper::dns()->addRecord($data['domain'], 'SRV', $data['subdomain'], [
            'service' => CfHelper::getTypeData($order->package->type)['service'],
            'proto' => CfHelper::getTypeData($order->package->type)['proto'],
            'name' => $data['subdomain'],
            'target' => $data['ip'],
            'port' => $data['port'],
        ]);
        
        return redirect()->back()->with('success', 'Domain has been updated');
    }

}
