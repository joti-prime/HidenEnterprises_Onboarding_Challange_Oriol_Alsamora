<?php

namespace App\Console\Commands;

use App\Models\Gateways\PayPalGateway;
use Illuminate\Console\Command;

class RemovePayPalWebhook extends Command
{
    protected $signature = 'paypal:remove-webhook';
    protected $description = 'Remove PayPal webhook ID from configuration';

    public function handle()
    {
        PayPalGateway::removeWebhookId();
    }
}