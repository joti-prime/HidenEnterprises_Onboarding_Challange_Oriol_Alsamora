<?php

namespace App\Services\UptimeRobot;

use App\Models\Order;
use App\Models\Package;
use App\Services\ServiceInterface;
use App\Services\UptimeRobot\Entities\UptimeRobotApi;

class Service implements ServiceInterface
{
    /**
     * Unique key used to store settings for this service.
     */
    public static string $key = 'uptimerobot';

    protected Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public static function metaData(): object
    {
        return (object) [
            'display_name' => 'UptimeRobot',
            'author' => 'HCTestDash',
            'version' => '1.0.0',
            'wemx_version' => ['*'],
        ];
    }

    /**
     * Admin-level configuration: the UptimeRobot API key, never hard-coded.
     */
    public static function setConfig(): array
    {
        return [
            [
                'key' => 'encrypted::uptimerobot::api_key',
                'name' => 'UptimeRobot API Key',
                'description' => 'Your UptimeRobot API v3 key (Integrations & API page in your UptimeRobot dashboard). Stored encrypted.',
                'type' => 'password',
                'rules' => ['required', 'string'],
            ],
        ];
    }

    /**
     * Admin-level package configuration: the default check interval for monitors created under this package.
     */
    public static function setPackageConfig(Package $package): array
    {
        return [
            [
                'key' => 'interval',
                'name' => 'Check interval (seconds)',
                'description' => 'How often UptimeRobot checks the monitor. 300 = 5 minutes (minimum on the free plan).',
                'type' => 'number',
                'default_value' => 300,
                'rules' => ['required', 'integer', 'min:60'],
            ],
        ];
    }

    /**
     * Checkout fields: the customer picks which URL they want monitored.
     */
    public static function setCheckoutConfig(Package $package): array
    {
        return [
            [
                'col' => 'w-1/2 p-2',
                'key' => 'monitor_name',
                'name' => 'Monitor name',
                'description' => 'A friendly name for your monitor',
                'type' => 'text',
                'rules' => ['required', 'string', 'max:100'],
            ],
            [
                'col' => 'w-1/2 p-2',
                'key' => 'monitor_url',
                'name' => 'Website URL to monitor',
                'description' => 'The full URL of the website you want us to watch, e.g. https://example.com',
                'type' => 'text',
                'rules' => ['required', 'url'],
            ],
        ];
    }

    /**
     * Quick-access button on the order page, linking straight to the Manage tab.
     */
    public static function setServiceButtons(Order $order): array
    {
        return [
            [
                'name' => 'Manage monitor',
                'color' => 'primary',
                'href' => route('service', ['order' => $order->id, 'page' => 'manage']),
            ],
        ];
    }

    /**
     * Custom client-facing routes for this service require permission entries
     * so members/subusers can be granted or denied access to them.
     */
    public static function permissions(): array
    {
        return [
            'uptimerobot.monitor.pause' => [
                'description' => 'Can this user pause the UptimeRobot monitor',
            ],
            'uptimerobot.monitor.resume' => [
                'description' => 'Can this user resume the UptimeRobot monitor',
            ],
            'uptimerobot.monitor.update' => [
                'description' => 'Can this user edit the UptimeRobot monitor',
            ],
        ];
    }

    /**
     * Provision: create a real monitor in UptimeRobot for the URL the customer chose.
     */
    public function create(array $data = [])
    {
        $api = new UptimeRobotApi();

        $name = $this->order->option('monitor_name') ?: $this->order->name;
        $url = $this->order->option('monitor_url');
        $interval = (int) ($this->order->package->data('interval', 300));

        $monitor = $api->createMonitor($name, $url, $interval);

        if (empty($monitor['id'])) {
            throw new \Exception('UptimeRobot did not return a monitor id when provisioning this service.');
        }

        $this->order->setExternalId((string) $monitor['id']);
    }

    /**
     * Suspend: pause the monitor in UptimeRobot (order expired or suspended by an admin).
     */
    public function suspend(array $data = [])
    {
        if (empty($this->order->external_id)) {
            return;
        }

        (new UptimeRobotApi())->pauseMonitor($this->order->getExternalId());
    }

    /**
     * Unsuspend: resume the monitor in UptimeRobot.
     */
    public function unsuspend(array $data = [])
    {
        if (empty($this->order->external_id)) {
            return;
        }

        (new UptimeRobotApi())->resumeMonitor($this->order->getExternalId());
    }

    /**
     * Terminate: permanently delete the monitor from UptimeRobot.
     */
    public function terminate(array $data = [])
    {
        if (empty($this->order->external_id)) {
            return;
        }

        try {
            (new UptimeRobotApi())->deleteMonitor($this->order->getExternalId());
        } catch (\Exception $e) {
            // If it was already removed directly in UptimeRobot, don't block termination locally.
            report($e);
        }
    }

    /**
     * Admin "Test connection" button (Section: service settings).
     */
    public static function testConnection()
    {
        try {
            (new UptimeRobotApi())->testConnection();
        } catch (\Exception $e) {
            return redirect()->back()->withError('Failed to connect to UptimeRobot. ' . $e->getMessage());
        }

        return redirect()->back()->withSuccess('Successfully connected with UptimeRobot.');
    }
}
