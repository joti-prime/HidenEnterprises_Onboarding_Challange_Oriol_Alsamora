<?php

namespace App\Console\Commands\User;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ResetPasswordCommand extends Command
{
    protected $description = 'Reset a user password';

    protected $signature = 'user:reset-password';

    /**
     * ResetPasswordCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $allUsers = User::all();
        $emails = $allUsers->pluck('email')->toArray();

        $userEmail = $this->anticipate('Please enter the user email', $emails);

        $user = User::where('email', $userEmail)->first();

        if (!$user) {
            $this->error('No user with this email found.');

            return;
        }

        $password = $this->secret('Please enter the new password');

        $user->password = Hash::make($password);
        $user->save();

        $this->info('Password reset successfully.');
    }
}
