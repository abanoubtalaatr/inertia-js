<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class IncompleteProfileNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        //
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => [
                'ar' => 'الملف الشخصي غير مكتمل',
                'en' => 'Your profile is incomplete',
            ],
            'message' => [
                'ar' => 'أكمل ملفك الشخصي الآن لجذب المزيد من العملاء',
                'en' => 'Complete it now to attract more clients',
            ],
            'type' => 'profile_incomplete',
        ];
    }
}
