<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Notifications\IncompleteProfileNotification;
use Illuminate\Console\Command;

class CheckIncompleteProfiles extends Command
{
    protected $signature = 'profiles:check-incomplete';

    protected $description = 'Check for incomplete user profiles and send notifications';

    public function handle()
    {
        try {
            $users = Account::whereDoesntHave('notifications', function ($query) {
                $query->where('type', 'profile_incomplete')
                    ->where('created_at', '>', now()->subDays(7));
            })->get();

            $this->info("Found {$users->count()} accounts to check");

            foreach ($users as $user) {
                try {
                    if ($user->hasIncompleteProfile()) {
                        $user->notify(new IncompleteProfileNotification);
                        $this->info("Notification sent to account {$user->id}");
                    }
                } catch (\Exception $e) {
                    $this->error("Error sending notification to account {$user->id}: ".$e->getMessage());
                    \Log::error('Notification error', [
                        'account_id' => $user->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            $this->error('Command failed: '.$e->getMessage());
            \Log::error('Check incomplete profiles command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
