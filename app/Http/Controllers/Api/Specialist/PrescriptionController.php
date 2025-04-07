<?php

namespace App\Http\Controllers\Api\Specialist;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PrescriptionRequest;
use App\Http\Resources\Api\PrescriptionResource;
use App\Models\Prescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrescriptionController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $prescriptions = Prescription::query()->where('specialist_id', Auth::id());

        if ($request->filled('client_id')) {
            $prescriptions->where('client_id', $request->client_id);
        }

        if ($request->has('paginate') && $request->input('paginate') == 1) {
            $data = PrescriptionResource::collection($prescriptions->paginate());

            return $this->success($data->response()->getData(true));
        }

        return $this->success(PrescriptionResource::collection($prescriptions->get()));
    }

    public function store(PrescriptionRequest $request)
    {
        $prescription = Prescription::create([
            'specialist_id' => Auth::id(),
            'client_id' => $request->client_id,
            'medication' => $request->medication,
            'dosage' => $request->dosage,
            'instructions' => $request->instructions,
        ]);

        return $this->success(PrescriptionResource::make($prescription));
    }
}
