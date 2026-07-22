<?php

namespace App\Events\Order;

use App\Jobs\SendWebhook;
use App\Models\Order;
use Illuminate\Support\Facades\Blade;

class OrderEvent
{
    public static function handle(Order $order, string $event): void
    {
        self::emails($order, $event);
        self::webhooks($order, $event);
    }

    public static function emails(Order $order, string $event): void
    {
        foreach ($order->package->emails->where('event', $event)->all() as $email) {
            $attachment = $email->attachment ?
            [
                'attachment' => [
                    [
                        'name' => basename($email->attachment),
                        'path' => $email->attachment,
                    ],
                ],
            ]
            : [];

            $email->title = self::replacePlaceholders($email->title, $order);
            $email->body = self::replacePlaceholders($email->body, $order);

            $email = $order->user->email(array_merge(
                [
                    'subject' => $email->title,
                    'content' => $email->body,
                ],
                $attachment
            ));
        }
    }

    public static function webhooks(Order $order, string $event): void
    {
        foreach ($order->package->webhooks->where('event', $event)->all() as $webhook) {

            $data = ($webhook->data) ? $webhook->data : [];
            $data = self::replaceArrayPlaceholders($data, $order);

            $headers = ($webhook->headers) ? $webhook->headers : [];
            $headers = self::replaceArrayPlaceholders($headers, $order);

            SendWebhook::dispatch($webhook->url, $webhook->method, $data, $headers);
        }
    }

    public static function replacePlaceholders($value, $order): string
    {
        $value = Blade::render(
            $value,
            ['order' => $order],
            deleteCachedView: true
        );

        return $value;
    }

    public static function replaceArrayPlaceholders($value, $order): array
    {
        $value = Blade::render(
            json_encode($value),
            ['order' => $order],
            deleteCachedView: true
        );

        return (array) json_decode($value);
    }
}
