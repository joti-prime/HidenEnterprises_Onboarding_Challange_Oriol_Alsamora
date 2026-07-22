<?php

namespace App\Entities;

use Exception;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class ZipManager
{
    protected ZipArchive $zip;

    public function __construct()
    {
        $this->zip = new ZipArchive;
    }

    /**
     * @throws Exception
     */
    public function resourceDownloadAndExtract($resource_id, $version_id): array
    {
        ini_set('memory_limit', '100M');

        $api = new ResourceApiClient();
        $resource = $api->getResource($resource_id)['data'];
        $extractTo = base_path($resource['category']);

        if ($resource['category'] == 'Templates'){
            $extractTo = base_path();
        }

        if ($resource['category'] == 'Services'){
            $extractTo = base_path('app/Services');
        }

        $data = $api->downloadResource($resource_id, $version_id);

        if ($data->status() == 403) {
            return ['status' => false, 'error' => $data->json()['error']];
        }

        $contentType = $data->header('Content-Type');
        if ($contentType !== 'zip') {
            return ['status' => false, 'error' => __('admin.downloaded_file_not_zip', ['content_type' => $contentType])];
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'zip');
        if ($tempFile === false) {
            return ['status' => false, 'error' => __('admin.unable_create_temporary_file')];
        }

        $result = file_put_contents($tempFile, $data->body());
        if ($result === false) {
            return ['status' => false, 'error' => __('admin.unable_write_temporary_file', ['temp_file' => $tempFile])];
        }

        $zip = new ZipArchive;
        $res = $zip->open($tempFile);

        if ($res === true) {
            $zip->extractTo($extractTo);
            $zip->close();
            unlink($tempFile);
        } else {
            unlink($tempFile);

            return ['status' => false, 'error' => __('admin.could_not_open_zip', ['temp_file' => $tempFile])];
        }

        return ['status' => true];
    }

    /**
     * @throws Exception
     */
    public function extractToDirectory(string $zipPath, string $extractPath): bool
    {
        if ($this->zip->open($zipPath) === true) {
            $this->zip->extractTo($extractPath);
            $this->zip->close();
            $this->recursiveUnzip($extractPath);

            return true;
        } else {
            throw new Exception(__('admin.unable_open_zip', ['zip_path' => $zipPath]));
        }
    }

    /**
     * @throws Exception
     */
    protected function recursiveUnzip(string $directory): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST,
            RecursiveIteratorIterator::CATCH_GET_CHILD
        );

        foreach ($iterator as $path => $dir) {
            if (str_ends_with($dir, '.zip')) {
                $subZip = new ZipArchive;
                if ($subZip->open($path) === true) {
                    $subZip->extractTo(dirname($path));
                    $subZip->close();
                    unlink($path);
                } else {
                    throw new Exception(__('admin.unable_open_sub_archive', ['path' => $path]));
                }
            }
        }
    }
}
