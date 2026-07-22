<?php

namespace App\Services;

use App\Models\Package;

interface ServiceInterface
{
    public static function metaData(): object;

    public static function setConfig(): array;

    public static function setPackageConfig(Package $package): array;

    public static function setCheckoutConfig(Package $package): array;

    public function create(array $data = []);

    public function suspend(array $data = []);

    public function unsuspend(array $data = []);

    public function terminate(array $data = []);
}
