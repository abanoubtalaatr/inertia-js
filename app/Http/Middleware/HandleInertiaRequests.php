<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => $request->user() ? [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'notificationCount' => $request->user()->unreadNotifications()->count(),
                'avatar' => $request->user()->avatar,
                'role' => $request->user()->role,
            ] : null,
            'flash' => [
                'success' => $request->session()->get('success'),
            ],
            'locale' => app()->getLocale(),
            'auth_permissions' => $request->user() ? $request->user()->getPermissionsViaRoles()->pluck('name')->toArray() : [],

        ];
    }
}
