<?php

if (!function_exists('cf')) {
    function cf(): string
    {
        return  \App\Services\CloudFlare\Entities\CfService::class;
    }
}

if (!function_exists('cfHelper')) {
    function cfHelper(): string
    {
        return  \App\Services\CloudFlare\Entities\CfHelper::class;
    }
}