<?php
/**
 * User: junade
 * Date: 01/02/2017
 * Time: 12:31
 */

namespace App\Services\CloudFlare\Sdk\Endpoints;

use App\Services\CloudFlare\Sdk\Adapter\Adapter;

interface API
{
    public function __construct(Adapter $adapter);
}
