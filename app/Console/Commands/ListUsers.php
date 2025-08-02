<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class ListUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all users in the system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::with('role')->get();
        
        $this->info('Users in database:');
        $this->info('==================');
        
        foreach ($users as $user) {
            $this->line($user->name . ' - ' . $user->email . ' - ' . $user->role->display_name);
        }
        
        $this->info('Total users: ' . $users->count());
    }
}
