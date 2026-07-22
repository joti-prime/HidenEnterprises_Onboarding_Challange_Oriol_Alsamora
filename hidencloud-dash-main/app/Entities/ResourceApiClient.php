<?php

namespace App\Entities;

use Illuminate\Support\Facades\Http;

class ResourceApiClient
{
    protected string $baseUrl;

    protected string $licenseKey;

    public function __construct()
    {
        $this->baseUrl = 'https://wemx.net/api/resources';
        $this->licenseKey = config('app.license');
    }

    public function getAllResources(?string $category = null, ?string $label = null, int $page = 1, ?string $sort = null): array

    {
        $response = Http::withOptions([
            'query' => [
                'category' => $category,
                'label' => $label,
                'page' => $page,
                'sort' => $sort,
                'license_key' => $this->licenseKey,
            ],
        ])->get("{$this->baseUrl}");

        return $response->json() ?? ['data' => [], 'error' => 'Request error'];
    }

    public function categories(): array
    {
        $response = Http::withOptions([
            'query' => [
                'license_key' => $this->licenseKey,
            ],
        ])->get("{$this->baseUrl}/categories");

        return $response->json() ?? ['data' => [], 'error' => 'Request error'];
    }

    public function getResource(int $id): array
    {
        $response = Http::withOptions([
            'query' => [
                'license_key' => $this->licenseKey,
            ],
        ])->get("{$this->baseUrl}/{$id}");

        if ($response->status() == 404) {
            return abort(404);
        }

        return $response->json();
    }

    public function downloadResource(int $id, int $versionId)
    {
        return Http::withOptions([
            'query' => [
                'license_key' => $this->licenseKey,
            ],
        ])->get("{$this->baseUrl}/download/{$id}/{$versionId}");
    }
}
