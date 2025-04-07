<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\BookingRequest;
use App\Http\Requests\Api\UpdateBookingStatusRequest;
use App\Http\Resources\Api\BookingResource;
use App\Models\Booking;
use App\Models\User;
use App\Notifications\BookingConfirmed;
use App\Notifications\BookingStatusUpdated;
use App\Notifications\NewBooking;
use App\Services\GoogleMeetService;
use App\Services\WorkingHoursService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    use ApiResponse;

    protected $workingHoursService;

    public function __construct(WorkingHoursService $workingHoursService)
    {
        $this->workingHoursService = $workingHoursService;
    }

    /**
     * Create a new booking.
     */
    public function store(BookingRequest $request)
    {
        $availableTimes = $this->workingHoursService->getAvailableTimes(Auth::id(), $request->date);
        $requestedStart = $request->start_time;
        $requestedEnd = $request->end_time;

        // Check availability
        $isAvailable = false;
        foreach ($availableTimes as $slot) {
            if ($slot['start'] <= $requestedStart && $slot['end'] >= $requestedEnd) {
                $isAvailable = true;
                break;
            }
        }

        if (! $isAvailable) {
            return $this->error('The requested time slot is not available.');
        }

        // Get specialist and client emails
        $specialist = User::findOrFail($request->specialist_id);
        $client = User::find(Auth::id());

        // Create Google Meet
        $meetService = new GoogleMeetService;
        $meeting = $meetService->createMeeting(
            "Consultation with {$specialist->name}",
            Carbon::parse($request->date.' '.$requestedStart)->toIso8601String(),
            Carbon::parse($request->date.' '.$requestedEnd)->toIso8601String(),
            [$specialist->email, $client->email]
        );

        // Create booking
        $booking = Booking::create([
            'client_id' => $client->id,
            'specialist_id' => $specialist->id,
            'date' => $request->date,
            'start_time' => $requestedStart,
            'end_time' => $requestedEnd,
            'status' => 'confirmed',
            'meet_link' => $meeting['meet_link'],
            'google_event_id' => $meeting['event_id'],
        ]);

        // Send notifications
        $client->notify(new BookingConfirmed($booking));
        $specialist->notify(new NewBooking($booking));

        return $this->success(BookingResource::make($booking), __('Booking created successfully.'), 201);
    }

    /**
     * Update booking status (e.g., confirm, cancel, reschedule).
     */
    /**
     * Update booking status (e.g., confirm, cancel, reschedule).
     */
    public function updateStatus(UpdateBookingStatusRequest $request, $bookingId)
    {
        $userId = Auth::id();
        $booking = Booking::with(['client', 'specialist'])
            ->where(function ($query) use ($userId) {
                $query->where('client_id', $userId)
                    ->orWhere('specialist_id', $userId);
            })
            ->where('id', $bookingId)
            ->firstOrFail();

        $originalStatus = $booking->status;
        $newStatus = $request->status;

        // Update the booking status
        $booking->update(['status' => $newStatus]);

        // Handle Google Meet based on status changes
        $meetService = new GoogleMeetService;

        if ($originalStatus !== 'confirmed' && $newStatus === 'confirmed') {
            // Create Google Meet for newly confirmed bookings
            try {
                $meeting = $meetService->createMeeting(
                    "Consultation with {$booking->specialist->name}",
                    Carbon::parse($booking->date.' '.$booking->start_time)->toIso8601String(),
                    Carbon::parse($booking->date.' '.$booking->end_time)->toIso8601String(),
                    [$booking->specialist->email, $booking->client->email]
                );

                $booking->update([
                    'meet_link' => $meeting['meet_link'],
                    'google_event_id' => $meeting['event_id'],
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create Google Meet: '.$e->getMessage());
                // Continue without failing the request
            }
        } elseif ($newStatus === 'cancelled' && $booking->google_event_id) {
            // Cancel Google Meet for cancelled bookings
            try {
                $meetService->cancelMeeting($booking->google_event_id);
                $booking->update(['meet_link' => null, 'google_event_id' => null]);
            } catch (\Exception $e) {
                Log::error('Failed to cancel Google Meet: '.$e->getMessage());
            }
        }

        // Send appropriate notifications
        $this->sendStatusNotifications($booking, $originalStatus, $newStatus);

        return $this->success(
            ['status' => $newStatus, 'meet_link' => $booking->meet_link],
            'Booking status updated successfully'
        );
    }

    /**
     * Send notifications based on status change
     */
    protected function sendStatusNotifications(Booking $booking, string $originalStatus, string $newStatus)
    {
        if ($originalStatus !== 'confirmed' && $newStatus === 'confirmed') {
            // Notify client about confirmation
            $booking->client->notify(new BookingConfirmed($booking));

            // Notify specialist with meet link
            $booking->specialist->notify(new BookingStatusUpdated(
                $booking,
                "Booking confirmed with {$booking->client->name}",
                "The booking has been confirmed. Meeting link: {$booking->meet_link}"
            ));
        } elseif ($newStatus === 'cancelled') {
            // Notify both parties about cancellation
            $message = 'Booking has been cancelled.';

            $booking->client->notify(new BookingStatusUpdated(
                $booking,
                'Booking cancelled',
                $message
            ));

            $booking->specialist->notify(new BookingStatusUpdated(
                $booking,
                'Booking cancelled',
                $message
            ));
        } elseif ($newStatus === 'rescheduled') {
            // Handle rescheduling notifications
            $message = 'Booking has been rescheduled.';

            $booking->client->notify(new BookingStatusUpdated(
                $booking,
                'Booking rescheduled',
                $message
            ));

            $booking->specialist->notify(new BookingStatusUpdated(
                $booking,
                'Booking rescheduled',
                $message
            ));
        }
    }

    public function index()
    {
        if (Auth::user()->role == 'specialist') {
            $bookings = Booking::where('specialist_id', Auth::id())->with('client')->paginate();
        } else {
            $bookings = Booking::where('client_id', Auth::id())->with('specialist')->paginate();
        }

        // Transform the bookings using BookingResource
        $bookingsResource = BookingResource::collection($bookings);

        // Get the paginated response data
        $paginatedResponse = $bookingsResource->response()->getData(true);

        // Add custom keys
        $paginatedResponse['status'] = true;
        $paginatedResponse['message'] = '';

        return $this->success($paginatedResponse);
    }
}
