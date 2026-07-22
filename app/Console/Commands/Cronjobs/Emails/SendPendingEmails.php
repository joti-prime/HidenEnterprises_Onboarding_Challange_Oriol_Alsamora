<?php

namespace App\Console\Commands\Cronjobs\Emails;

use App\Mail\ClientEmail;
use App\Models\EmailHistory;
use App\Models\ErrorLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class SendPendingEmails extends Command
{
    protected $signature = 'cron:emails:send';

    protected $description = 'Send pending emails';

    public function handle()
    {
        // store cronjob cache as an indicator that cronjobs are active
        Cache::put('cron_active', true, 300);

        $pending_emails = EmailHistory::getPendingEmails();

        $this->info('Pending Emails: '. $pending_emails->count());
        $progressBar = $this->output->createProgressBar(count($pending_emails));
        $progressBar->start();

        foreach ($pending_emails as $email) {

            // try to send the email
            try {
                app()->setLocale($email->user->language ?? settings('language', 'en'));
                Mail::to($email->receiver)->send(new ClientEmail($email));
                $email->wasSent();

            } catch (\Exception $error) {
                // Catch any exceptions thrown by the service handler
                // Handle the error appropriately and register it as an event
                ErrorLog::updateOrCreate([
                    'source' => 'cron:emails:send',
                    'severity' => 'ERROR',
                    'message' => 'Automatic Mailer was unable to proccess '. $pending_emails->count() . ' pending emails. Please ensure a valid SMTP server has been setup.',
                ]);
            }

            $progressBar->advance($email->id);
        }

        $progressBar->finish();
        $this->line(''); // Move to the next line after progress bar completion

        $this->info('Task Completed: all pending emails were sent');

    }
}
