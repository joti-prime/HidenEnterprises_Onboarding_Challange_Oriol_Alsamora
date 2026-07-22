<?php

namespace App\Services\CloudFlare\Sdk\Traits;

trait BodyAccessorTrait
{
    private $body;

    public function getBody()
    {
        return $this->body;
    }
}
