<?php

namespace App\Http\Controllers\Api\Client;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\PrescriptionResource;
use App\Models\Prescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrescriptionController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $prescriptions = Prescription::where('client_id', Auth::id());

        if ($request->has('paginate') && $request->input('paginate') == 1) {
            $data = PrescriptionResource::collection($prescriptions->paginate());

            return $this->success($data->response()->getData(true));
        }

        return $this->success(PrescriptionResource::collection($prescriptions->get()));
    }

    public function show(Prescription $prescription)
    {
        if ($prescription->client_id !== Auth::id()) {
            return $this->error('Unauthorized');
        }

        return $this->success(PrescriptionResource::make($prescription));
    }
}
