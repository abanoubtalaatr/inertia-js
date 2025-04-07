<?php

namespace App\Http\Controllers\Api\Specialist;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\MedicalHistoryResource;
use App\Http\Resources\Api\PrescriptionResource;
use App\Http\Resources\Api\TreatmentPlanResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ConsultationController extends Controller
{
    use ApiResponse;

    public function show(User $client)
    {
        return response()->json([
            // 'medical_history' => new MedicalHistoryResource($client->medicalHistory),
            'treatment_plans' => TreatmentPlanResource::collection($client->treatmentPlans),
            // 'prescriptions' => PrescriptionResource::collection($client->prescriptions),
            // 'bookings' => $client->bookings()->where('specialist_id', Auth::id())->get(),
        ]);
    }
}
