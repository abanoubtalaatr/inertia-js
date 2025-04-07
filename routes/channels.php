<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// اhotel
Broadcast::channel('Hotel.{id}', function ($user, $id) {
    return (int) $user->hotel_id === (int) $id;
});

// provider
Broadcast::channel('Provider.{id}', function ($user, $id) {
    return $user->isProvider() && (int) $user->id === (int) $id;
});

// contract
Broadcast::channel('Contract.{contractId}', function ($user, $contractId) {
    $contract = \App\Models\Contract::find($contractId);

    if (! $contract) {
        return false;
    }

    // السماح للفندق صاحب العقد
    if ($user->hotel_id === $contract->hotel_id) {
        return true;
    }

    // السماح لمزودي الخدمة المرتبطين بالعقد
    return $contract->subServices()
        ->where('provider_id', $user->id)
        ->exists();
});
