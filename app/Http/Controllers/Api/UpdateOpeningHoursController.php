<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateOpeningHoursRequest;

class UpdateOpeningHoursController extends Controller
{
    public function __invoke(UpdateOpeningHoursRequest $request)
    {
        $request->user()->update(['opening_hours_data' => $request->opening_hours_data]);

        return 'dnoe';
    }
}
