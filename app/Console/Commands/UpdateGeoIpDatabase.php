<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UpdateGeoIpDatabase extends Command
{
    protected $signature = 'geoip:update {--force : Re-download even if the current file is from this month}';

    protected $description = 'Download the latest DB-IP Country Lite database into storage/app/geoip/Country.mmdb';

    public function handle(): int
    {
        $dir = storage_path('app/geoip');
        $dest = $dir.'/Country.mmdb';

        if (! is_dir($dir) && ! mkdir($dir, 0755, true) && ! is_dir($dir)) {
            $this->error("Cannot create directory: {$dir}");
            return self::FAILURE;
        }

        if (! $this->option('force') && is_file($dest)) {
            $age = time() - filemtime($dest);
            if ($age < 25 * 86400) {
                $this->info('GeoIP database is less than 25 days old. Skip. Use --force to override.');
                return self::SUCCESS;
            }
        }

        // DB-IP publishes the new month around day 1-2. If the current month's
        // file is not yet available, fall back to the previous month.
        $now = Carbon::now();
        $months = [$now->copy()->format('Y-m'), $now->copy()->subMonth()->format('Y-m')];

        foreach ($months as $month) {
            $url = "https://download.db-ip.com/free/dbip-country-lite-{$month}.mmdb.gz";
            $this->info("Trying {$url}");

            $tmpGz = $dir.'/Country.mmdb.gz.tmp';
            $tmpMmdb = $dir.'/Country.mmdb.tmp';

            try {
                $response = Http::timeout(120)->withOptions(['sink' => $tmpGz])->get($url);
            } catch (\Throwable $e) {
                $this->warn("Request failed: {$e->getMessage()}");
                @unlink($tmpGz);
                continue;
            }

            if (! $response->successful()) {
                $this->warn("HTTP {$response->status()} for {$month}");
                @unlink($tmpGz);
                continue;
            }

            if (! is_file($tmpGz) || filesize($tmpGz) < 100_000) {
                $this->warn("Downloaded file looks too small for {$month}");
                @unlink($tmpGz);
                continue;
            }

            if (! $this->gunzip($tmpGz, $tmpMmdb)) {
                @unlink($tmpGz);
                @unlink($tmpMmdb);
                continue;
            }

            @unlink($tmpGz);

            if (! rename($tmpMmdb, $dest)) {
                $this->error('Failed to move temporary file into place');
                @unlink($tmpMmdb);
                return self::FAILURE;
            }

            $size = number_format(filesize($dest) / 1024 / 1024, 2);
            $this->info("GeoIP database updated ({$month}, {$size} MB)");
            Log::info('GeoIP database updated', ['month' => $month, 'bytes' => filesize($dest)]);
            return self::SUCCESS;
        }

        $this->error('Could not download a GeoIP database from any month');
        return self::FAILURE;
    }

    protected function gunzip(string $src, string $dest): bool
    {
        $in = @gzopen($src, 'rb');
        if ($in === false) {
            $this->error('Failed to open gzip file');
            return false;
        }
        $out = @fopen($dest, 'wb');
        if ($out === false) {
            gzclose($in);
            $this->error('Failed to open destination file');
            return false;
        }
        while (! gzeof($in)) {
            $chunk = gzread($in, 1024 * 1024);
            if ($chunk === false) {
                gzclose($in);
                fclose($out);
                return false;
            }
            fwrite($out, $chunk);
        }
        gzclose($in);
        fclose($out);
        return true;
    }
}
