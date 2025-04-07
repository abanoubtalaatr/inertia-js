<?php

namespace App\Console\Commands;

use App\Jobs\SendNotificationJob;
use App\Models\AdminNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendScheduledNotifications extends Command
{
    protected $signature = 'notifications:send-scheduled';

    protected $description = 'Send scheduled notifications';

    public function handle()
    {
        $now = Carbon::now();
        \Log::info('Running scheduled notifications check at: '.$now);

        $notifications = AdminNotification::where('status', 'scheduled')
            ->where('scheduled_at', '<=', $now)
            ->get();

        \Log::info('Found '.$notifications->count().' notifications to send');

        foreach ($notifications as $notification) {
            \Log::info('Processing notification ID: '.$notification->id);
            $notification->update(['status' => 'sent']);
            SendNotificationJob::dispatch($notification);
        }
    }
}
