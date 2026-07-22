<?php
/**
 * User: junade
 * Date: 13/01/2017
 * Time: 16:52
 */

namespace App\Services\CloudFlare\Sdk\Auth;

interface Auth
{
    public function getHeaders(): array;
}
