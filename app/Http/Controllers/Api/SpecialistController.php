<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\SimpleUserResource;
use App\Models\User;
use Illuminate\Http\Request;

class SpecialistController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
            'paginate' => 'nullable|in:0,1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = User::query()->where('role', 'specialist');

        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('bio', 'LIKE', "%{$searchTerm}%");
            });
        }

        if ($request->has('paginate') && $request->input('paginate') == 1) {
            $perPage = $request->input('per_page', 15); // Default to 15 items per page
            $users = SimpleUserResource::collection($query->paginate($perPage));
            $users = $users->response()->getData(true);

            return $this->success($users);
        }

        $users = SimpleUserResource::collection($query->get());

        return $this->success($users);
    }
}
