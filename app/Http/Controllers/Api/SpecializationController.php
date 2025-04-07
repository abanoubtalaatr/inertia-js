<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\SpecializationResource;
use App\Models\Specialization;
use Illuminate\Http\Request;

class SpecializationController extends Controller
{
    public function index(Request $request)
    {
        $specializations = SpecializationResource::collection(Specialization::with('translations')->get());

        return ApiResponse::success($specializations, 'Specializations retrieved successfully');
    }
}
