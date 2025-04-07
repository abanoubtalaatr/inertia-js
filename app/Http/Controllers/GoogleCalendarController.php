<?php

namespace App\Http\Controllers;

use Google\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GoogleCalendarController extends Controller
{
    public function getClient()
    {
        $client = new Client;
        $client->setApplicationName(config('app.name'));
        $client->setScopes([
            Google_Service_Calendar::CALENDAR,
            Google_Service_Calendar::CALENDAR_EVENTS,
        ]);
        $client->setAuthConfig(storage_path('app/google-calendar/oauth-credentials.json'));
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        // Load previously authorized token if it exists
        if (Storage::exists('google-calendar/oauth-token.json')) {
            $accessToken = json_decode(Storage::get('google-calendar/oauth-token.json'), true);
            $client->setAccessToken($accessToken);

            // Refresh token if expired
            if ($client->isAccessTokenExpired()) {
                if ($client->getRefreshToken()) {
                    $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                    Storage::put('google-calendar/oauth-token.json', json_encode($client->getAccessToken()));
                }
            }
        }

        return $client;
    }

    public function redirectToGoogle()
    {
        $client = $this->getClient();
        $authUrl = $client->createAuthUrl();

        return redirect()->away($authUrl);
    }

    public function handleGoogleCallback(Request $request)
    {
        $client = $this->getClient();
        $client->fetchAccessTokenWithAuthCode($request->code);

        // Store the token
        Storage::put('google-calendar/oauth-token.json', json_encode($client->getAccessToken()));

        return redirect('/')->with('status', 'Google Calendar authentication successful!');
    }
}
