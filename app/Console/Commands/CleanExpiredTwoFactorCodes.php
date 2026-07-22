<?php

namespace App\Console\Commands;

use App\Models\UsedTwoFactorCode;
use Illuminate\Console\Command;

class CleanExpiredTwoFactorCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twofactor:clean-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean expired two-factor authentication codes';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $deleted = UsedTwoFactorCode::cleanExpired();

        $this->info("Cleaned {$deleted} expired two-factor codes.");

        return Command::SUCCESS;
    }
}