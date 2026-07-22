<?php

namespace App\Listeners\Webhooks;

use App\Events\UserDeleted;
use Illuminate\Support\Facades\Http;

class UserDeletedWebhook
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
    public function handle(UserDeleted $event): void
    {
        $user = $event->user;

        $response = Http::post(settings('event_webhook_url'), [
            // Message
            'content' => 'User Deleted',

            // Embeds Array
            'embeds' => [
                [
                    // Embed Title
                    'title' => 'User has been deleted',

                    // add url
                    'url' => route('users.edit', $user->id),

                    // Embed Type
                    'type' => 'rich',

                    // Embed left border color in HEX
                    'color' => hexdec('ff3939'),

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
                        [
                            'name' => 'Balance',
                            'value' => price($user->balance),
                            'inline' => true,
                        ],
                        [
                            'name' => 'Total Orders',
                            'value' => $user->orders->count(),
                            'inline' => true,
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
    public function failed(UserDeleted $event, Throwable $exception): void
    {
        ErrorLog('UserDeletedWebhook', $exception->getMessage());
    }
}
