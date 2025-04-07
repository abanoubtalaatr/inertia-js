<?php

namespace App\Jobs;

use App\Models\AdminNotification;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $adminNotification;

    public $tries = 3;

    public $timeout = 120;

    public $maxExceptions = 3;

    public function __construct(AdminNotification $adminNotification)
    {
        $this->adminNotification = $adminNotification;
    }

    public function handle()
    {
        // DB::transaction(function () {
        $recipients = $this->getRecipients($this->adminNotification);

        if ($recipients->isEmpty()) {
            Log::warning('No recipients found for notification: '.$this->adminNotification?->id);

            return;
        }

        $notifications = [];
        foreach ($recipients as $user) {
            $notifications[] = [
                'id' => Str::uuid(),
                'notifiable_type' => $user->getMorphClass(),
                'notifiable_id' => $user->id,
                'type' => 'system_notification',
                'data' => json_encode([
                    'title' => $this->adminNotification?->title,
                    'message' => $this->adminNotification?->message,
                    'admin_notification_id' => $this->adminNotification->id,
                    'recipient_type' => $this->adminNotification->recipient_type,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Notification::insert($notifications);
        $this->adminNotification->update(['status' => 'sent']);
        // }, 5);
    }

    public function failed(\Throwable $exception)
    {
        Log::error('Job failed: '.$exception->getMessage());
    }

    private function getRecipients($adminNotification)
    {
        try {
            $query = User::query();

            if ($adminNotification->recipient_type === 'all') {
                // For 'all', we need to get all active users with their respective conditions
                return $query->where('is_active', 1)
                    ->where(function ($q) {
                        $q->where(function ($q) {
                            // Companies
                            $q->where('role', 'company')
                                ->where('is_active', 1);
                        })->orWhere(function ($q) {
                            // Specialists
                            $q->where('role', 'specialist')
                                ->where('is_active', 1);
                        })->orWhere(function ($q) {
                            // Clients
                            $q->where('role', 'user')
                                ->where('verified', 1)
                                ->where('status', 'accepted')
                                ->where('is_suspend', 0)
                                ->whereDoesntHave('roles');
                        });
                    })
                    ->get();
            }

            $recipientIds = $adminNotification->recipient_id
                ? json_decode($adminNotification->recipient_id, true)
                : null;

            $sendToAllOfType = is_null($recipientIds) || in_array('all', $recipientIds);

            return match ($adminNotification->recipient_type) {
                'companies' => $sendToAllOfType
                    ? $query->where('role', 'company')
                        ->where('is_active', 1)
                        ->get()
                    : $query->where('role', 'company')
                        ->where('is_active', 1)
                        ->whereIn('id', $recipientIds)
                        ->get(),
                'specialists' => $sendToAllOfType
                    ? $query->where('role', 'specialist')
                        ->where('is_active', 1)
                        ->get()
                    : $query->where('role', 'specialist')
                        ->where('is_active', 1)
                        ->whereIn('id', $recipientIds)
                        ->get(),
                'clients' => $sendToAllOfType
                    ? $query->where('role', 'user')
                        ->where('verified', 1)
                        ->where('status', 'accepted')
                        ->where('is_suspend', 0)
                        ->whereDoesntHave('roles')
                        ->get()
                    : $query->where('role', 'user')
                        ->where('verified', 1)
                        ->where('status', 'accepted')
                        ->where('is_suspend', 0)
                        ->whereDoesntHave('roles')
                        ->whereIn('id', $recipientIds)
                        ->get(),
                default => collect([])
            };
        } catch (\Exception $e) {
            Log::error('Error fetching recipients: '.$e->getMessage());

            return collect([]);
        }
    }
}
