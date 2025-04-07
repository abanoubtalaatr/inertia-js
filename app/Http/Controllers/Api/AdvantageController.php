<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\AdvantageResource;
use App\Models\Advantage;

class AdvantageController extends Controller
{
    use ApiResponse;

    public function index()
    {
        return $this->success(AdvantageResource::collection(Advantage::all()));
    }

    public function show() {}
}
