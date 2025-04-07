<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ToggleSuspendController extends Controller
{
    use ApiResponse;

    public function __invoke(Request $request)
    {

        $request->user()->update(['is_suspend' => ! $request->user()->is_suspend]);

        return $this->success([], 'updated successfully.', 200);
    }
}
