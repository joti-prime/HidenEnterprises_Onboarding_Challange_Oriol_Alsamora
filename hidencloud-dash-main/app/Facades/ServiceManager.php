<?php

namespace App\Facades;

use App\Models\Order;
use App\Models\Package;
use Nwidart\Modules\Facades\Module;

class ServiceManager
{
    public $service;

    public $class;

    public $config;

    public function __construct($service) {
        $this->service = Module::find($service);

        if (!$this->service) {
            throw new \Exception('Could not locate Module');
        }

        if (!class_exists("\\App\\Services\\{$this->service->getStudlyName()}\\Service")) {
            throw new \Exception("\\App\\Services\\{$this->service->getStudlyName()}\\Service class does not exists in {$this->service->getName()}");
        }

        $this->class = app("\\App\\Services\\{$this->service->getStudlyName()}\\Service");
        $this->config = $this->about();
    }

    public function module()
    {
        return $this->service;
    }

    public function about(): object
    {
        return $this->class->metaData();
    }

    public function hasConfig(): bool
    {
        return $this->getConfig()->isNotEmpty();
    }

    public function getDisplayName(): string
    {
        if (method_exists($this->class, 'getDisplayName')) {
            return $this->class->getDisplayName();
        }

        return $this->config->name;
    }

    public function getConfig()
    {
        return collect($this->class->setConfig());
    }

    public function getConfigRules()
    {
        return $this->getConfig()->mapWithKeys(function ($item) {
            $key = $item['key'];
            if (str_contains($key, '[]')) {
                $key = str_replace('[]', '', $key);

                return [$key . '.*' => $item['rules']]; // Validation of each element of the array
            } elseif (preg_match('/\[(.*?)\]/', $key, $matches)) {
                $key = str_replace('[' . $matches[1] . ']', '', $key);

                return [$key . '.' . $matches[1] => $item['rules']]; // Validation of a specific array key
            } else {
                return [$key => $item['rules']]; // Normal field validation
            }
        })->toArray();
    }

    public function hasPackageConfig(Package $package): bool
    {
        return $this->getPackageConfig($package)->isNotEmpty();
    }

    public function getPackageConfig(Package $package)
    {
        return collect($this->class->setPackageConfig($package));
    }

    public function getPackageRules(Package $package, array $only = [], ?string $prefix = null)
    {
        return $this->getPackageConfig($package)->mapWithKeys(function ($item) use ($only, $prefix) {
            $key = $item['key'];

            // If only specific keys are requested, skip the ones not in the array
            if (!empty($only) && !in_array($key, $only)) {
                return [];
            }

            if ($prefix) {
                $key = $prefix . '.' . $item['key'];
            }

            if (str_contains($key, '[]')) {
                $key = str_replace('[]', '', $key);

                return [$key . '.*' => $item['rules']]; // Validation of each element of the array
            } elseif (preg_match('/\[(.*?)\]/', $key, $matches)) {
                $key = str_replace('[' . $matches[1] . ']', '', $key);

                return [$key . '.' . $matches[1] => $item['rules']]; // Validation of a specific array key
            } else {
                return [$key => $item['rules']]; // Normal field validation
            }
        })->toArray();
    }

    public function getPackageRule(Package $package, string $key, ?string $format = null)
    {
        // first check if the key is in the package config
        $config = $this->getPackageRules($package);

        // if the key has [] in it, it means it's an array, we if its empty make it key.* to validate each element else key.x.y
        if (str_contains($key, '[]')) {
            $key = str_replace('[]', '.*', $key);
        } elseif (preg_match('/\[(.*?)\]/', $key, $matches)) {
            $key = str_replace('[' . $matches[1] . ']', '', $key);
        }

        if (!array_key_exists($key, $config)) {
            return null;
        }

        $rule = $config[$key];

        // check if format is set and if the key is already in that format, if not format it
        if ($format) {
            if ($format == 'array' and !is_array($rule)) {
                $rule = explode('|', $rule);
            } elseif ($format == 'string' and is_array($rule)) {
                $rule = implode('|', $rule);
            }
        }

        return $rule;
    }

    public function hasCheckoutConfig(Package $package): bool
    {
        return $this->getCheckoutConfig($package)->isNotEmpty();
    }

    public function getCheckoutConfig(Package $package)
    {
        return collect($this->class->setCheckoutConfig($package));
    }

    public function getCheckoutRules(Package $package)
    {
        return $this->getCheckoutConfig($package)->mapWithKeys(function ($item) {
            $key = $item['key'];
            if (str_contains($key, '[]')) {
                $key = str_replace('[]', '', $key);

                return [$key . '.*' => $item['rules']]; // Validation of each element of the array
            } elseif (preg_match('/\[(.*?)\]/', $key, $matches)) {
                $key = str_replace('[' . $matches[1] . ']', '', $key);

                return [$key . '.' . $matches[1] => $item['rules']]; // Validation of a specific array key
            } else {
                return [$key => $item['rules']]; // Normal field validation
            }
        })->toArray();
    }

    public function getServiceButtons(Order $order)
    {
        if (method_exists($this->class, 'setServiceButtons')) {
            return collect($this->class->setServiceButtons($order));
        }

        return collect([]);
    }

    public function getServiceSidebarButtons(Order $order)
    {
        if (method_exists($this->class, 'setServiceSidebarButtons')) {
            return collect($this->class->setServiceSidebarButtons($order));
        }

        return collect([]);
    }

    public function permissions()
    {
        $permissions = [
            'manage' => [
                'description' => 'Can this user view the "manage" page for this order',
            ],
            'invoices' => [
                'description' => 'Can this user view the "invoices" page for this order',
            ],
            'renew' => [
                'description' => 'Can this user renew this order, create invoices and pay invoices',
            ],
            'cancel' => [
                'description' => 'Can this user cancel this order',
            ],
            'cancel-undo' => [
                'description' => 'Can this user undo cancellations for this order',
            ],
            'upgrade' => [
                'description' => 'Can this user upgrade or downgrade this order',
            ],
            'members' => [
                'description' => 'Can this user view members for this order',
            ],
            'invite-member' => [
                'description' => 'Can this user invite members for this orders and manage their permissions',
            ],
            'update-member' => [
                'description' => 'Can this user update members for this orders and manage their permissions',
            ],
            'delete-member' => [
                'description' => 'Can this user delete members for this orders',
            ],
        ];

        if ($this->canUpgrade()) {
            $permissions = array_merge($permissions, [
                'upgrade' => [
                    'description' => 'Can this user upgrade or downgrade this order',
                ],
            ]);
        }

        if ($this->canChangePassword()) {
            $permissions = array_merge($permissions, [
                'change-password' => [
                    'description' => 'Can this user change external passwords for this order',
                ],
            ]);
        }

        if ($this->canLoginToPanel()) {
            $permissions = array_merge($permissions, [
                'login-to-panel' => [
                    'description' => 'Can this user automatically login to the panel for this order',
                ],
            ]);
        }

        if (method_exists($this->class, 'permissions')) {
            $service_permissions = $this->class->permissions();
        }

        return collect(array_merge($permissions, $service_permissions ?? []));
    }

    public function pages()
    {
        $pages = [];

        if (method_exists($this->class, 'pages')) {
            $pages = $this->class->pages();
        }

        return collect($pages);
    }

    public function canUpgrade(): bool
    {
        if (method_exists($this->class, 'upgrade')) {
            return true;
        }

        return false;
    }

    public function canChangePassword(): bool
    {
        if (method_exists($this->class, 'changePassword')) {
            return true;
        }

        return false;
    }

    public function canLoginToPanel(): bool
    {
        if (method_exists($this->class, 'loginToPanel')) {
            return true;
        }

        return false;
    }

    public function canTestConnection(): bool
    {
        if (method_exists($this->class, 'testConnection')) {
            return true;
        }

        return false;
    }

    /**
     * Triggers the checkout event for the service.
     * This is called when the user clicks the checkout button.
     * If returns an array with an error key, the user will be redirected back with the error message.
     */
    public function eventCheckout(Package $package): void
    {
        if (method_exists($this->class, 'eventCheckout')) {
            $resp = $this->class->eventCheckout($package);
            if (is_array($resp) and array_key_exists('error', $resp)) {
                redirect()->back()->with('error', $resp['error'])->send();
            }
        }
    }

    /**
     * Triggers the load package event for the service.
     * This is called when the package view page is loaded.
     * If returns an array with an error key, the user will be redirected back with the error message.
     */
    public function eventLoadPackage(Package $package): void
    {
        if (method_exists($this->class, 'eventLoadPackage')) {
            $resp = $this->class->eventLoadPackage($package);
            if (is_array($resp) and array_key_exists('error', $resp)) {
                redirect()->back()->with('error', $resp['error'])->send();
            }
        }
    }
}
