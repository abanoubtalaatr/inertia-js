<?php

namespace App\Http\Controllers\API;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\RatingResource;
use App\Models\Booking;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        // Validate the request inputs
        $request->validate([
            'user_id' => 'nullable|exists:users,id', // Filter by user who gave the rating
            'paginate' => 'nullable|in:0,1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        // Start building the query for ratings where the specialist is the authenticated user
        $query = Rating::where('specialist_id', Auth::id());

        // Filter by user_id if provided
        if ($request->has('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        // Check if pagination is requested
        if ($request->has('paginate') && $request->input('paginate') == 1) {
            $perPage = $request->input('per_page', 15); // Default to 15 items per page
            $ratings = RatingResource::collection($query->paginate($perPage));
            $ratingsData = $ratings->response()->getData(true); // Get paginated data as array

            return $this->success($ratingsData, 'Ratings retrieved successfully');
        }

        // Without pagination
        $ratings = RatingResource::collection($query->get());
        if ($ratings->isEmpty()) {
            return $this->success([], 'No ratings found yet');
        }

        return $this->success($ratings, 'Ratings retrieved successfully');
    }

    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'booking_id' => 'nullable|exists:bookings,id',
            'rating' => 'required|integer|between:1,5',
            'feedback' => 'required|string|max:500',
        ]);

        // Check if the booking exists and belongs to the authenticated user
        $booking = Booking::where('id', $request->booking_id)
            ->where('client_id', Auth::id())
            ->firstOrFail();

        // Check if the booking has already been rated
        if ($booking->rating) {
            return $this->error('This booking has already been rated');
        }

        // Create the rating
        $rating = Rating::create([
            'user_id' => Auth::id(),
            'specialist_id' => $booking->specialist_id,
            'booking_id' => $request->booking_id,
            'rating' => $request->rating,
            'feedback' => $request->feedback,
        ]);

        return $this->success([], 'Rating submitted successfully');
    }
}
