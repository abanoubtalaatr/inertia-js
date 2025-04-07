<?php

namespace App\Providers;

use App\Models\Notification as CustomNotification;
use App\Models\User;
use App\Observers\PermissionObserver;
use App\Observers\RoleObserver;
use App\Observers\UserObserver;
use App\Repositories\AdvantageRepository;
use App\Repositories\AdvantageRepositoryInterface;
use App\Repositories\Interfaces\PageRepositoryInterface;
use App\Repositories\PageRepository;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        $this->app->bind(SpatieRole::class, Role::class);
        $this->app->bind(PageRepositoryInterface::class, PageRepository::class);
        $this->app->bind(AdvantageRepositoryInterface::class, AdvantageRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ini_set('memory_limit', env('PHP_MEMORY_LIMIT', '1024M'));

        // dd(session()->get('locale'));
        User::observe(UserObserver::class);
        Role::observe(RoleObserver::class);
        Permission::observe(PermissionObserver::class);
        App::setLocale(Session::get('locale', config('app.locale')));

        Relation::enforceMorphMap([
            'App\Models\User' => \App\Models\User::class,
            'App\Models\Role' => \App\Models\Role::class,
            'App\Models\Settings' => \App\Models\Setting::class,
            'App\Models\Page' => \App\Models\Page::class,
            'App\Models\PageSection' => \App\Models\PageSection::class,
            'App\Models\Notification' => \App\Models\Notification::class,
            'App\Models\Account' => \App\Models\Account::class,
        ]);

        if (! file_exists(storage_path('app/contracts'))) {
            mkdir(storage_path('app/contracts'), 0755, true);
        }

        //     DatabaseNotification::creating(function ($notification) {
        //     $data = is_array($notification->data)
        //     ? $notification->data
        //     : json_decode($notification->data, true);
        //     CustomNotification::create([
        //         'title' => $data['title'] ?? 'New Notification',
        //         'message' => $data['message'] ?? 'You have a new notification.',
        //         'recipient_type' => 'individual',
        //         'recipient_id' => $notification->notifiable_id,
        //         'status' => 'sent',
        //         'scheduled_at' => null,
        //     ]);
        // });
    }
}
