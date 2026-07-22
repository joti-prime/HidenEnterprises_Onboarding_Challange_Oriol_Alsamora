<?php

namespace App\Console\Commands\Setup;

use App\Traits\EnvironmentWriterTrait;
use Illuminate\Console\Command;

use function Laravel\Prompts\alert;
use function Laravel\Prompts\confirm;

class CloudflareEnable extends Command
{
    use EnvironmentWriterTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cloudflare:enable';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enable CloudFlare integration';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            if (config('laravelcloudflare.enabled')) {
                // confirm whether users wants to disable it
                $confirmed = confirm('Cloudflare is already enabled. Do you want to disable it?', false);
                if ($confirmed) {
                    $this->writeToEnvironment(['LARAVEL_CLOUDFLARE_ENABLED' => false]);

                    // clear cache
                    $this->call('config:clear');
                    $this->warn('Cloudflare has been disabled.');
                }

                return;
            }

            $this->writeToEnvironment(['LARAVEL_CLOUDFLARE_ENABLED' => true]);

            // clear cache
            $this->call('config:clear');

            $this->info('Cloudflare has been enabled.');
        } catch (\Exception $e) {
            alert($e->getMessage());
            alert('Something went wrong...');
            alert('Please ensure you are logged in as root and the wemx directory is has the correct permissions.');
        }
    }
}
