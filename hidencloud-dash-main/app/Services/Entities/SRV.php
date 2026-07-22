<?php

namespace App\Services\CloudFlare\Entities;

use Cloudflare\API\Adapter\Guzzle;
use Cloudflare\API\Auth\APIKey;
use Cloudflare\API\Endpoints\DNS;
use Cloudflare\API\Endpoints\Zones;
use Illuminate\Support\Collection;

class SRV
{
    public static function apiKey(): APIKey
    {
        return new APIKey(settings('cloudflare::email', ''), settings('encrypted::cloudflare::api_key', ''));
    }

    public static function adapter(): Guzzle
    {
        return new Guzzle(self::apiKey());
    }

    public static function dns(): DNS
    {
        return new DNS(self::adapter());
    }

    public static function findRecordByName($name, $zone_name = ''): array
    {
        $zones = new Zones(self::adapter());
        $response = $zones->listZones(name: $zone_name);
        $domains = [];
        foreach ($response->result as $zone) {
            $records = self::dns()->listRecords($zone->id);
            foreach ($records->result as $record) {
                if ($record->name == $name) {
                    // Object to array
                    return json_decode(json_encode($record), true);
                }
            }
        }
        return [];
    }

    public static function getDomainsList(): Collection
    {
        $zones = new Zones(self::adapter());
        $response = $zones->listZones();
        $domains = [];
        foreach ($response->result as $zone) {
            $domains[$zone->id] = $zone->name;
        }
        return collect($domains);
    }

    public static function srvExist($subdomain, $domain): bool
    {
        $zones = new Zones(self::adapter());
        $response = $zones->listZones();
        foreach ($response->result as $zone) {
            $records = self::dns()->listRecords($zone->id);
            foreach ($records->result as $record) {
                if ($record->name == $subdomain . '.' . $domain) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function createSrvDNSRecords(PterodactylOrderDomains $data): void
    {
        self::dns()->addRecord($data->domain_data['id'], 'A', $data->domain_data['subdomain'], $data->domain_data['ip'], 0, false);
        $srvData = [
            'name' => $data->domain_data['subdomain'],
            'service' => $data->domain_data['service'],
            'proto' => $data->domain_data['proto'],
            'priority' => 0,
            'weight' => 0,
            'port' => $data->domain_data['port'],
            'target' => $data->domain_data['subdomain'] . '.' . $data->domain_data['domain'],
        ];
        self::dns()->addRecord(
            $data->domain_data['id'],
            'SRV',
            $data->domain_data['service'] . '.' . $data->domain_data['proto'] . '.' . $data->domain_data['subdomain'],
            $data->domain_data['subdomain'] . '.' . $data->domain_data['domain'],
            0,
            false,
            0,
            data: $srvData
        );
        $cf['sub'] = self::findRecordByName(self::getSubDomainName($data->domain_data), $data->domain_data['domain']);
        $cf['srv'] = self::findRecordByName(self::getSrvName($data->domain_data), $data->domain_data['domain']);
        $data->domain_data = array_merge($data->domain_data, $cf);
        $data->save();
    }

    public static function deleteSrvDNSRecords(PterodactylOrderDomains $data): void
    {
        foreach (self::dns()->listRecords($data->domain_data['id'])->result as $record) {
            if ($record->name == self::getSubDomainName($data->domain_data)) {
                self::dns()->deleteRecord($data->domain_data['id'], $record->id);
            }
            if ($record->name == self::getSrvName($data->domain_data)) {
                self::dns()->deleteRecord($data->domain_data['id'], $record->id);
            }
        }
    }

    public static function getTypeData($type = 'minecraft'): array
    {
        $types = [
            'minecraft' => [
                'service' => '_minecraft',
                'proto' => '_tcp',
            ],
            'bedrock' => [
                'service' => '_minecraft',
                'proto' => '_udp',
            ],
            'ts3' => [
                'service' => '_ts3',
                'proto' => '_udp',
            ],
            'csgo' => [
                'service' => '_csgo',
                'proto' => '_udp',
            ],
            'rust' => [
                'service' => '_rust',
                'proto' => '_tcp',
            ],
            'gmod' => [
                'service' => '_gmod',
                'proto' => '_udp',
            ],
            'ark' => [
                'service' => '_ark',
                'proto' => '_tcp',
            ],
            'factorio' => [
                'service' => '_factorio',
                'proto' => '_tcp',
            ],
            'terraria' => [
                'service' => '_terraria',
                'proto' => '_tcp',
            ],
            'squad' => [
                'service' => '_squad',
                'proto' => '_udp',
            ],
            'arma3' => [
                'service' => '_arma3',
                'proto' => '_udp',
            ],
            'fivem' => [
                'service' => '_fivem',
                'proto' => '_udp',
            ],
            'other' => [
                'service' => '_other',
                'proto' => '_tcp',
            ],
        ];
        return $types[$type];

    }









    public static function getSrvName(array $data): string
    {
        $service = $data['service'] ?? '_minecraft';
        $proto = $data['proto'] ?? '_tcp';
        return $service . '.' . $proto . '.' . $data['subdomain'] . '.' . $data['domain'];
    }

    public static function getSubDomainName(array $data): string
    {
        return $data['subdomain'] . '.' . $data['domain'];
    }
}