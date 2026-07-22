<?php

namespace App\Console\Commands\Setup;

use App\Traits\EnvironmentWriterTrait;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Kernel;

class AppSettingsCommand extends Command
{
    use EnvironmentWriterTrait;

    protected $description = 'Configure basic environment settings for your application.';

    protected $signature = 'setup:environment
                            {--url= : The URL that this Panel is running on.}
                            {--timezone= : The timezone to use for Panel times.}';

    protected array $variables = [];

    /**
     * AppSettingsCommand constructor.
     */
    public function __construct(private Kernel $console)
    {
        parent::__construct();
    }

    /**
     * Handle command execution.
     *
     * @throws \Exception
     */
    public function handle(): int
    {
        $this->info('The application URL MUST begin with https:// or http:// depending on if you are using SSL or not. If you do not include the scheme your emails and other content will link to the wrong location.');
        $this->variables['APP_URL'] = $this->option('url') ?? $this->ask(
            'Application URL',
            config('app.url', 'https://example.com')
        );

        $this->info('The timezone should match one of PHP\'s supported timezones. If you are unsure, please reference https://php.net/manual/en/timezones.php.');
        $this->variables['APP_TIMEZONE'] = $this->option('timezone') ?? $this->anticipate(
            'Application Timezone',
            \DateTimeZone::listIdentifiers(),
            config('app.timezone')
        );

        // Make sure session cookies are set as "secure" when using HTTPS
        if (!str_starts_with($this->variables['APP_URL'], 'http')) {
            $this->error('Application URL must start with https:// or http:// please try again.');

            return 0;
        }

        if (str_ends_with($this->variables['APP_URL'], '/')) {
            $this->error('Application URL cannot end with "/" please try again.');

            return 0;
        }

        // Make sure session cookies are set as "secure" when using HTTPS
        if (str_starts_with($this->variables['APP_URL'], 'https://')) {
            $this->variables['SESSION_SECURE_COOKIE'] = 'true';
        }

        $this->writeToEnvironment($this->variables);

        $this->info($this->console->output());
        $this->info('Environment has been updated');

        return 0;
    }
}
