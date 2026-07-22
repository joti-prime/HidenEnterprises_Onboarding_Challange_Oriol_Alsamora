<?php

namespace App\Console\Commands\Cronjobs\Emails;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use App\Models\MassMail;

class SendMassMails extends Command
{
    protected $signature = 'cron:emails:send-mass-mails';

    protected $description = 'Send pending mass emails';

    public function handle()
    {
        $emails = MassMail::whereIn('status', ['pending', 'scheduled'])->get();

        $this->info('Loaded mass emails: '. $emails->count());

        foreach ($emails as $email) {
            // check if scheduled at is set and if its in the future, skip
            if ($email->scheduled_at && $email->scheduled_at->isFuture()) {
                $this->info('Skipping email '. $email->id .' as its scheduled for '. $email->scheduled_at);
                continue;
            }

            $audience = $email->audience();

            try {
                $this->info('Sending email to '. $audience->count() .' users');
            } catch(\Exception $error) {
                ErrorLog('cron:emails:send-mass-mails', "Failed to load audience for email {$email->id}");
                $email->update(['status' => 'failed']);
                continue;
            }

            $email->update(['status' => 'processing']);

            foreach ($audience as $user) {
                try {
                    $emailData = [
                        'subject' => $email->subject,
                        'content' => $email->content,
                    ];
    
                    if($email->button_text AND $email->button_url) {
                        $emailData['button'] = [
                            'name' => $email->button_text,
                            'url' => $email->button_url,
                        ];
                    }
    
                    $user->email($emailData);
    
                    // increment the sent count
                    $email->increment('sent_count');
                } catch(\Exception $error) {
                    ErrorLog('cron:emails:send-mass-mails', 'Automatic Mailer was unable to proccess '. $emails->count() . ' pending emails. Please ensure a valid SMTP server has been setup.');
                    $email->update(['status' => 'failed']);
                    continue;
                }
            }

            // if repeat is set, update the scheduled_at
            if ($email->repeat) {
                $email->update([
                    'sent_count' => 0,
                    'status' => 'scheduled',
                    'scheduled_at' => now()->addDays($email->repeat),
                    'last_completed_at' => now(),
                ]);
            } else {
                $email->update([
                    'status' => 'completed',
                    'last_completed_at' => now(),
                ]);
            }

            $this->info('Email sent to '. $audience->count() .' users');
        }
    }
}
