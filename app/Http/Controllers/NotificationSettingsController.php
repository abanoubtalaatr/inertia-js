<?php

// app/Http/Controllers/NotificationSettingsController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationSettingsController extends Controller
{
    public function index()
    {

        // Fetch current settings from database or config
        $settings = [
            'email_notifications' => auth()->user()->email_notifications ?? '0',
        ];

        return Inertia::render('NotificationSettings', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'email_notifications' => 'required|in:0,1',
        ]);

        // Update user settings in database
        auth()->user()->update([
            'email_notifications' => $validated['email_notifications'],
        ]);

        return back()->with('success', 'Settings updated successfully');
    }
}
