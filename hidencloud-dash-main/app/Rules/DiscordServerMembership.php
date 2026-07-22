<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Modules\DiscordConnect\Services\Discord;

class DiscordServerMembership implements Rule
{
    protected $serverId;
    protected $packageId;
    protected $errorMessage;

    public function __construct($serverId, $packageId = null)
    {
        $this->serverId = $serverId;
        $this->packageId = $packageId;
    }

    public function passes($attribute, $value)
    {
        $user = auth()->user();
        
        // Check if user has Discord connected
        $discordOauth = $user->oauthService('discord')->first();
        
        if (!$discordOauth) {
            $this->errorMessage = 'You must connect your Discord account before getting this free service.';
            return false;
        }

        // Get Discord user ID from OAuth data
        $discordUserId = $discordOauth->data->id ?? null;
        
        if (!$discordUserId) {
            $this->errorMessage = 'Unable to retrieve your Discord user ID. Please reconnect your Discord account.';
            return false;
        }

        // Check if Discord module is available
        if (!class_exists('\Modules\DiscordConnect\Services\Discord')) {
            $this->errorMessage = 'Discord verification service is not available.';
            return false;
        }

        // Check if user is in the Discord server
        try {
            $discord = new Discord();
            if (!$discord->isUserInServer($discordUserId)) {
                $this->errorMessage = 'You must be a member of our Discord server: https://discord.hidencloud.com to get this free service.';
                return false;
            }
        } catch (\Exception $e) {
            \Log::error('Discord server membership check failed', [
                'user_id' => $user->id,
                'discord_user_id' => $discordUserId,
                'error' => $e->getMessage()
            ]);
            $this->errorMessage = 'Unable to verify Discord server membership. Please try again later.';
            return false;
        }

        return true;
    }

    public function message()
    {
        return $this->errorMessage ?: 'Discord server membership verification failed.';
    }
}