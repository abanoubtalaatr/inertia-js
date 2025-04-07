<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\MedicalHistoryResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MedicalHistoryController extends Controller
{
    public function show()
    {
        $medicalHistory = Auth::user()->medicalHistory;

        return new MedicalHistoryResource($medicalHistory);
    }

    public function update(Request $request)
    {
        $request->validate(['details' => 'required|string']);
        $medicalHistory = Auth::user()->medicalHistory()->updateOrCreate(
            ['client_id' => Auth::id()],
            ['details' => $request->details]
        );

        return new MedicalHistoryResource($medicalHistory);
    }
}
