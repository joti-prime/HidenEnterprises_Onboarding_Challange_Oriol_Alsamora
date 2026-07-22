<?php

namespace App\Console\Commands\User;

use App\Models\Address;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateUserCommand extends Command
{
    protected $description = 'Creates a user on the system via the CLI.';

    protected $signature = 'user:create {--email=} {--username=} {--first_name=} {--last_name=} {--password=}';

    /**
     * CreateUserCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Handle command request to create a new user.
     *
     * @throws \Exception
     * @throws \Pterodactyl\Exceptions\Model\DataValidationException
     */
    public function handle()
    {
        $email = $this->option('email') ?? $this->ask('Email Address');
        $username = $this->option('username') ?? $this->ask('Username');
        $first_name = $this->option('first_name') ?? $this->ask('First Name');
        $last_name = $this->option('last_name') ?? $this->ask('Last Name');

        if (is_null($password = $this->option('password'))) {
            $password = $this->secret('Password');
        }

        $user = User::create([
            'username' => $username,
            'email' => $email,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'password' => Hash::make($password),
            'status' => 'active',
        ]);

        $this->emptyAddress($user);

        $this->table(['Field', 'Value'], [
            ['First Name', $user->first_name],
            ['Last Name', $user->last_name],
            ['Username', $user->username],
            ['Email', $user->email],
            ['Admin', $user->is_admin() ? 'Yes' : 'No'],
        ]);
    }

    private function emptyAddress($user)
    {
        $address = new Address();
        $address->user_id = $user->id;
        $address->company_name = '';
        $address->address = '';
        $address->address_2 = '';
        $address->country = '';
        $address->city = '';
        $address->region = '';
        $address->zip_code = '';
        $address->save();
    }
}
