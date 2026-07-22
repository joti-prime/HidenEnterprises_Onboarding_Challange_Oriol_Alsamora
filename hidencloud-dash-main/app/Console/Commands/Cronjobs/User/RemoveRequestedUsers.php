<?php

namespace App\Console\Commands\Cronjobs\User;

use App\Models\UserDelete;
use Illuminate\Console\Command;

class RemoveRequestedUsers extends Command
{
    protected $description = 'Deletes users that have requested for their accounts to be deleted.';

    protected $signature = 'user:remove-requested';

    /**
     * RemoveRequestedUsers constructor.
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
        $requests = UserDelete::where('status', 'pending')->get();
        foreach ($requests as $request) {
            if ($request->delete_at->isPast()) {
                $request->status = 'deleted';
                $request->save();
                $request->user->terminate();
            }
        }
    }
}
