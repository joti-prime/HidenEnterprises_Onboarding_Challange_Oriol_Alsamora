<?php

namespace App\Services\CloudFlare;

use App\Services\CloudFlare\Entities\CfHelper;
use App\Services\ServiceInterface;
use App\Models\Package;
use App\Models\Order;
use App\Services\CloudFlare\Sdk\Auth\APIKey;
use App\Services\CloudFlare\Sdk\Endpoints\DNS;
use App\Services\CloudFlare\Sdk\Endpoints\Zones;

class Service implements ServiceInterface
{
    public static string $key = 'cloudflare';
    private DNS $dns;
    private Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->dns = new DNS(CfHelper::adapter());
    }

    public static function metaData(): object
    {
        return (object)
        [
            'display_name' => 'CloudFlare',
            'author' => 'GIGABAIT',
            'version' => '1.0.0',
            'wemx_version' => ['dev', '>=2.0.0'],
        ];
    }

    public static function setConfig(): array
    {
        return [
            [
                'key' => 'encrypted::cloudflare::api_key',
                'name' => 'Global Api Key',
                'description' => 'Enter your CloudFlare Global Api Key',
                'type' => 'password',
                'rules' => ['required', 'string']
            ],
            [
                'key' => 'cloudflare::email',
                'name' => 'Email',
                'description' => 'Enter your CloudFlare Email',
                'type' => 'text',
                'rules' => ['required', 'email']
            ],
        ];
    }

    public static function setPackageConfig(Package $package): array
    {
        return [
            [
                'key' => 'domains[]',
                'name' => 'Domains',
                'description' => 'Select the domains you want to manage with CloudFlare',
                'multiple' => true,
                'type' => 'select',
                'options' => CfHelper::getDomainsList()->transform(fn($name, $id) => ['key' => $id, 'name' => $name])->toArray(),
                'rules' => ['required', 'string']
            ],
        ];
    }

    public static function setCheckoutConfig(Package $package): array
    {
        $domainsKeys = $package->data['domains'] ?? [];
        $domains = CfHelper::getDomainsList()->filter(fn($name, $id) => in_array($id, $domainsKeys))->toArray();
        return [
            [
                'col' => 'w-1/3 p-2',
                'key' => 'subdomain',
                'name' => 'Subdomain',
                'description' => 'Enter the subdomain you want to manage with CloudFlare',
                'type' => 'text',
                'rules' => ['required', 'string']
            ],
            [
                'col' => 'w-1/3 p-2',
                'key' => 'domains',
                'name' => 'Domains',
                'description' => 'Select the domains you want to manage with CloudFlare',
                'type' => 'select',
                'options' => $domains,
                'rules' => ['required', 'string']
            ],
            [
                'col' => 'w-1/3 p-2',
                'key' => 'ip',
                'name' => 'IP Address',
                'description' => 'Enter the IP Address of the server',
                'type' => 'text',
                'rules' => ['required', 'ip']
            ]
        ];
    }

    public static function setServiceButtons(Order $order): array
    {
        return [];
    }

    public function eventCheckout(Package $package): array
    {
        $subdomain = request('subdomain');
        $domain = request('domains');
        $records = $this->dns->listRecords($domain);
        foreach ($records->result as $record) {
            if ($record->name == $subdomain . '.' . $record->zone_name) {
                ErrorLog('cloudflare::' . $subdomain . '.' . $record->zone_name, 'This subdomain is already in use: ' . $subdomain . '.' . $record->zone_name, 'ERROR');
                redirect()->back()->withError('This subdomain is already in use')->send();
            }
        }
        return ['success' => true];
    }

    public function create(array $data = []): void
    {
        $data = $this->order->options;
        $this->dns->addRecord($data['domains'], 'A', $data['subdomain'], $data['ip']);
    }

    public function upgrade(Package $oldPackage, Package $newPackage)
    {

    }

    public function suspend(array $data = []): void
    {
        $this->deleteRecord($this->order->options);
    }

    public function unsuspend(array $data = []): void
    {
        $data = $this->order->options;
        $this->dns->addRecord($data['domains'], 'A', $data['subdomain'], $data['ip']);
    }

    public function terminate(array $data = []): void
    {
        $this->deleteRecord($this->order->options);
    }

    public static function testConnection()
    {
        $zones = new Zones(CfHelper::adapter());
        try {
            $zones->listZones(perPage: 100);
            return redirect()->back()->withSuccess("Successfully connected with CloudFlare");
        } catch (\Exception $e) {
            return redirect()->back()->withError("Failed to connect with CloudFlare: " . $e->getMessage());
        }
    }

    private function deleteRecord($data): void
    {
        foreach ($this->dns->listRecords($data['domains'])->result as $record) {
            if ($record->name == $data['subdomain'] . '.' . $record->zone_name) {
                $this->dns->deleteRecord($data['domains'], $record->id);
            }
        }
    }

}
