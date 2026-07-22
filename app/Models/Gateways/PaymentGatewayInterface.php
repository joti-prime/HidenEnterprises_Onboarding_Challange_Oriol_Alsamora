<?php

namespace App\Models\Gateways;

use App\Models\Payment;
use Illuminate\Http\Request;

/**
 * Summary of PaymentGatewayInterface
 */
interface PaymentGatewayInterface
{
    public static function processGateway(Gateway $gateway, Payment $payment);

    public static function returnGateway(Request $request);

    public static function processRefund(Payment $payment, array $data);

    public static function checkSubscription(Gateway $gateway, $subscriptionId): bool;

    public static function drivers(): array;

    public static function endpoint(): string;

    public static function getConfigMerge(): array;
}
