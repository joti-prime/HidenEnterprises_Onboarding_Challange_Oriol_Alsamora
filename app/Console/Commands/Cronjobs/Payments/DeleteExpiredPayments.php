<?php

namespace App\Console\Commands\Cronjobs\Payments;

use App\Models\Payment;
use Illuminate\Console\Command;

class DeleteExpiredPayments extends Command
{
    protected $signature = 'cron:payments:delete-expired';

    protected $description = 'Delete payments that are expired and unpaid';

    public function handle()
    {
        $expired_payments = Payment::getExpiredPayments();

        $this->info('Loaded a list of unpaid & expired payments: '. $expired_payments->count());
        $progressBar = $this->output->createProgressBar(count($expired_payments));
        $progressBar->start();

        foreach ($expired_payments as $payment) {

            if ($payment->handler !== null) {
                $payment->handler()->onPaymentExpired($payment);
            }

            $payment->delete();

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->line(''); // Move to the next line after progress bar completion

        $this->info('Task Completed: all expired payments were removed');

    }
}
