<?php

namespace App\Console\Commands\Setup;

use App\Traits\EnvironmentWriterTrait;
use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Support\Str;

class EmailSettingsCommand extends Command
{
    use EnvironmentWriterTrait;

    protected $description = 'Set or update the email sending configuration for your application.';

    protected $signature = 'setup:mail
                            {--driver= : The mail driver to use.}
                            {--email= : Email address that messages from the Panel will originate from.}
                            {--from= : The name emails from the Panel will appear to be from.}
                            {--encryption=}
                            {--host=}
                            {--port=}
                            {--endpoint=}
                            {--username=}
                            {--password=}';

    protected array $variables = [];

    /**
     * EmailSettingsCommand constructor.
     */
    public function __construct(private ConfigRepository $config)
    {
        parent::__construct();
    }

    /**
     * Handle command execution.
     *
     * @throws \Exception
     */
    public function handle()
    {
        $this->variables['MAIL_DRIVER'] = $this->option('driver') ?? $this->choice(
            'Which driver should be used for sending emails?',
            [
                'smtp' => 'SMTP Server',
                'mail' => 'PHP\'s Internal Mail Function',
                'mailgun' => 'Mailgun Transactional Email',
                'mandrill' => 'Mandrill Transactional Email',
                'postmark' => 'Postmark Transactional Email',
            ],
            $this->config->get('mail.default', 'smtp')
        );

        $method = 'setup' . Str::studly($this->variables['MAIL_DRIVER']) . 'DriverVariables';
        if (method_exists($this, $method)) {
            $this->{$method}();
        }

        $this->variables['MAIL_FROM_ADDRESS'] = $this->option('email') ?? $this->ask(
            'Email address emails should originate from',
            $this->config->get('mail.from.address')
        );

        $this->variables['MAIL_FROM_NAME'] = $this->option('from') ?? $this->ask(
            'Name that emails should appear from',
            $this->config->get('mail.from.name')
        );

        $this->writeToEnvironment($this->variables);

        $this->line('Updating stored environment configuration file.');
        $this->line('');
    }

    /**
     * Handle variables for SMTP driver.
     */
    private function setupSmtpDriverVariables()
    {
        $this->variables['MAIL_HOST'] = $this->option('host') ?? $this->ask(
            'SMTP Host (e.g. smtp.gmail.com)',
            $this->config->get('mail.mailers.smtp.host')
        );

        $this->variables['MAIL_PORT'] = $this->option('port') ?? $this->ask(
            'SMTP Port',
            $this->config->get('mail.mailers.smtp.port')
        );

        $this->variables['MAIL_USERNAME'] = $this->option('username') ?? $this->ask(
            'SMTP Username',
            $this->config->get('mail.mailers.smtp.username')
        );

        $this->variables['MAIL_PASSWORD'] = $this->option('password') ?? $this->secret(
            'SMTP Password'
        );

        $this->variables['MAIL_ENCRYPTION'] = $this->option('encryption') ?? $this->choice(
            'Encryption method to use',
            ['tls' => 'TLS', 'ssl' => 'SSL', '' => 'None'],
            $this->config->get('mail.mailers.smtp.encryption', 'tls')
        );
    }

    /**
     * Handle variables for mailgun driver.
     */
    private function setupMailgunDriverVariables()
    {
        $this->variables['MAILGUN_DOMAIN'] = $this->option('host') ?? $this->ask(
            'Mailgun Domain',
            $this->config->get('services.mailgun.domain')
        );

        $this->variables['MAILGUN_SECRET'] = $this->option('password') ?? $this->ask(
            'Mailgun Secret',
            $this->config->get('services.mailgun.secret')
        );

        $this->variables['MAILGUN_ENDPOINT'] = $this->option('endpoint') ?? $this->ask(
            'Mailgun Endpoint',
            $this->config->get('services.mailgun.endpoint')
        );
    }

    /**
     * Handle variables for mandrill driver.
     */
    private function setupMandrillDriverVariables()
    {
        $this->variables['MANDRILL_SECRET'] = $this->option('password') ?? $this->ask(
            'Mandrill Secret',
            $this->config->get('services.mandrill.secret')
        );
    }

    /**
     * Handle variables for postmark driver.
     */
    private function setupPostmarkDriverVariables()
    {
        $this->variables['MAIL_DRIVER'] = 'smtp';
        $this->variables['MAIL_HOST'] = 'smtp.postmarkapp.com';
        $this->variables['MAIL_PORT'] = 587;
        $this->variables['MAIL_USERNAME'] = $this->variables['MAIL_PASSWORD'] = $this->option('username') ?? $this->ask(
            'Postmark API Key',
            $this->config->get('mail.username')
        );
    }
}
