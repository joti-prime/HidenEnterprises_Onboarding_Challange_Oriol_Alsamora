<?php

namespace App\Console\Commands\Modules;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeService extends Command
{
    protected $signature = 'service:make {name}';

    protected $description = 'Create a service';

    protected string $path = 'app/Services';

    protected string $storage_path = 'storage/';

    protected string $branch = 'https://github.com/WemXPro/service-example/archive/refs/heads/main.zip';

    public function handle(): void
    {
        $name = Str::studly($this->argument('name'));
        $name_lower = Str::lower($name);

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $name)) {
            $this->error("$name contains invalid characters");

            return;
        }

        $fullPath = base_path("{$this->path}/$name");
        if (File::exists($fullPath)) {
            $this->error("$name already exists");

            return;
        }

        if (!File::isWritable(base_path($this->storage_path))) {
            $this->error(base_path($this->storage_path). ' is not writable');

            return;
        }

        $zipFile = "{$this->storage_path}/$name.zip";
        $extractPath = "{$this->storage_path}/$name";

        File::put($zipFile, file_get_contents($this->branch));

        $zip = new \ZipArchive();
        if ($zip->open($zipFile) === true) {
            $zip->extractTo($extractPath);
            $zip->close();
        } else {
            $this->error("Could not open $zipFile");

            return;
        }

        File::delete($zipFile);

        // The replacement operations
        $this->replaceInFiles("{$extractPath}/service-example-main", 'Example', $name);
        $this->replaceInFiles("{$extractPath}/service-example-main", 'example', $name_lower);

        // Rename the service provider file
        File::move(
            "{$extractPath}/service-example-main/Providers/ExampleServiceProvider.php",
            "{$extractPath}/service-example-main/Providers/{$name}ServiceProvider.php"
        );

        // Move the directory
        File::moveDirectory("{$extractPath}/service-example-main", $fullPath);
        File::deleteDirectory($extractPath);

        $this->info("Created {$name}");
    }

    protected function replaceInFiles($directory, $search, $replace): void
    {
        $files = File::allFiles($directory);
        foreach ($files as $file) {
            $this->replaceInFile($file->getPathname(), $search, $replace);
        }
    }

    protected function replaceInFile($filePath, $search, $replace): void
    {
        $contents = File::get($filePath);
        $contents = str_replace($search, $replace, $contents);
        File::put($filePath, $contents);
    }
}
