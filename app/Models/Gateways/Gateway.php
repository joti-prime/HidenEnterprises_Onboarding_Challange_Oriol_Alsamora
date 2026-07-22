<?php

namespace App\Models\Gateways;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Nwidart\Modules\Facades\Module;
use Omnipay\Omnipay;

/**
 * App\Models\Gateways\Gateway
 *
 * @property int $id
 * @property string $name
 * @property string $type
 * @property string $driver
 * @property array $config
 * @property PaymentGatewayInterface $class
 * @property string $endpoint
 * @property int $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Gateway extends Model
{
    protected $table = 'gateways';

    protected $fillable = [
        'name',
        'type',
        'driver',
        'config',
        'class',
        'endpoint',
        'status',
        'refund_support',
        'blade_edit_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'config',
    ];

    protected $casts = ['config' => 'array'];

    public static string $currency = 'USD';

    /**
     * Retrieve all active gateway settings
     */
    public static function getActive(string $type = 'once'): Collection
    {
        return self::query()->where('status', 1)->where('type', $type)->orWhere('type', 'once/subscription')->get();
    }

    /**
     * Decrypt config when it is requested
     *
     * @return mixed|void
     */
    public function getConfigAttribute($value)
    {
        try {
            return decrypt($value);
        } catch (DecryptException $e) {
            //             return $value;
        }
    }

    /**
     * Encrypt and store the config
     */
    public static function storeConfig(Request $request, Gateway $gateway): void
    {
        $data = $request->all();
        $config = $gateway->config();
        foreach ($config as $key => $value) {
            if (array_key_exists($key, $data)) {
                $config[$key] = $data[$key];
            }
        }

        $gateway->config = encrypt($config);
        $gateway->save();
    }

    /**
     * Retrieve configuration data of the gateway
     */
    public function config(): array
    {

        if (empty($this->config)) {
            $this->config = encrypt(array_merge($this->getDefaultConfig(), $this->class::getConfigMerge()));
        }

        if (!is_array($this->config)) {
            $this->config = decrypt($this->config);
        }

        return $this->config;
    }

    /**
     * Retrieve default config parameters of the driver specified
     */
    public function getDefaultConfig(): array
    {
        try {
            $gateway = Omnipay::create($this->driver);

            return $gateway->getDefaultParameters();
        } catch (\Throwable $th) {
            return [];
        }
    }

    /**
     * Retrieve appropriate gateway
     */
    public static function getGateway(string $gatewayDriver): mixed
    {
        $gatewaySetting = self::query()->where('driver', $gatewayDriver)->firstOrFail();
        try {
            $config = $gatewaySetting->config;
            foreach ($config as $key => $value) {
                if ($value === 'true' || $value === 'false') {
                    $config[$key] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                }
            }

            return Omnipay::create($gatewaySetting->driver)->initialize($config);
        } catch (\Throwable $th) {
            return $gatewaySetting;
        }
    }

    /**
     * Retrieve all the available gateway drivers
     */
    public static function drivers(): array
    {
        $modules = [];
        foreach (Module::allEnabled() as $module) {
            if ($module->json()->get('type') == 'gateway') {
                $modules = array_merge($modules, $module->json()->get('class')::drivers());
            }
        }

        return array_merge(
            PayPalGateway::drivers(),
            PayPalCheckoutGateway::drivers(),
            PaddleGateway::drivers(),
            BitpaveGateway::drivers(),
            //            StripeGateway::drivers(),
            StripeSubscriptionGateway::drivers(),
            BalanceGateway::drivers(),
            StripeCheckoutGateway::drivers(),
            TebexGateway::drivers(),
            TebexSubscriptionGateway::drivers(),
            $modules
        );
    }
}
