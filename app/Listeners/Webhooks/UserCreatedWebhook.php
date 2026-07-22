<?php

namespace App\Listeners\Webhooks;

use App\Events\UserCreated;
use Illuminate\Support\Facades\Http;

class UserCreatedWebhook
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserCreated $event): void
    {
        $user = $event->user;

        $response = Http::post(settings('event_webhook_url'), [
            // Message
            'content' => 'User Created',

            // Embeds Array
            'embeds' => [
                [
                    // Embed Title
                    'title' => 'User has been created',

                    // add url
                    'url' => route('users.edit', $user->id),

                    // Embed Type
                    'type' => 'rich',

                    // Embed left border color in HEX
                    'color' => hexdec('059669'),

                    // Additional Fields array
                    'fields' => [
                        [
                            'name' => 'User ID',
                            'value' => "#{$user->id}",
                            'inline' => false,
                        ],
                        [
                            'name' => 'Full Name',
                            'value' => "{$user->fullname}",
                            'inline' => false,
                        ],
                        [
                            'name' => 'Username',
                            'value' => "{$user->username}",
                            'inline' => false,
                        ],
                        [
                            'name' => 'Email',
                            'value' => "{$user->email}",
                            'inline' => false,
                        ],
                        [
                            'name' => 'Country',
                            'value' => $user->address->country ?? 'N/A',
                            'inline' => false,
                        ],
                        // Etc..
                    ],
                ],
            ],

        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(UserCreated $event, Throwable $exception): void
    {
        ErrorLog('UserCreatedWebhook', $exception->getMessage());
    }
}
