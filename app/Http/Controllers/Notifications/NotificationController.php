<?php

namespace App\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use App\Jobs\SendNotificationJob;
use App\Models\AdminNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = AdminNotification::query()
            ->when($request->search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->date, function ($query, $date) {
                $query->whereDate('created_at', $date);
            })
            ->with('creator')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications,
            'filters' => $request->only(['search', 'status', 'date']),
        ]);
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'recipient_type' => 'required|in:all,specialists,clients,companies',
            'recipient_ids' => 'nullable|array',
            'recipient_ids.*' => 'nullable', // Changed to string to allow 'all' value
            'scheduled_at' => 'nullable|date|after:now',
            'status' => 'required|in:draft,scheduled,sent',
        ]);

        $status = $request->scheduled_at ? 'scheduled' : $request->status;

        // Handle 'all' selection for specific recipient types
        $recipientIds = $validated['recipient_ids'];
        if (in_array('all', $recipientIds)) {
            $recipientIds = ['all'];
        }

        $adminNotification = AdminNotification::create([
            'id' => Str::uuid(),
            'title' => $validated['title'],
            'message' => $validated['message'],
            'recipient_type' => $validated['recipient_type'],
            'recipient_id' => ($validated['recipient_type'] === 'all' || in_array('all', $recipientIds))
                ? null
                : json_encode($recipientIds),
            'status' => $validated['status'],
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'created_by' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        if (! $request->scheduled_at && $request->status === 'sent') {
            SendNotificationJob::dispatch($adminNotification);
        } else {
            SendNotificationJob::dispatch($adminNotification);
        }

        return redirect()->route('notifications.index')
            ->with('success', 'Notification created successfully.');
    }

    public function update(Request $request, AdminNotification $notification)
    {
        if ($notification->status === 'sent') {
            return redirect()->back()->withErrors('Cannot edit sent notifications.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'recipient_type' => 'required|string|in:all,providers,hotels,individual',
            'recipient_ids' => 'nullable|array',
            'recipient_ids.*' => 'exists:users,id',
            'status' => 'required|string|in:draft,scheduled,sent',
            'scheduled_at' => 'nullable|date',
        ]);
        $status = $request->scheduled_at ? 'scheduled' : $request->status;

        $notification->update([
            'title' => $validated['title'],
            'message' => $validated['message'],
            'recipient_type' => $validated['recipient_type'],
            'recipient_id' => $validated['recipient_type'] === 'individual'
                ? json_encode($validated['recipient_ids'])
                : null,
            'status' => $validated['status'],
            'scheduled_at' => $validated['scheduled_at'] ?? null,
        ]);

        if ($notification->status === 'sent') {
            SendNotificationJob::dispatch($notification);
        } elseif ($notification->status === 'scheduled' && $notification->scheduled_at) {
            SendNotificationJob::dispatch($notification)->delay($notification->scheduled_at);
        }

        if (! $request->scheduled_at && $request->status === 'sent') {
            SendNotificationJob::dispatch($notification);
        }

        return redirect()->route('notifications.index')->with('success', 'Notification updated successfully.');
    }

    public function destroy(AdminNotification $notification)
    {
        if (! in_array($notification->status, ['draft', 'scheduled'])) {
            return redirect()->back()->withErrors('Cannot delete sent notifications.');
        }

        $notification->delete();

        return redirect()->route('notifications.index')->with('success', 'Notification deleted successfully.');
    }

    public function create()
    {
        $companies = User::select(['id', 'name', 'email'])->where('role', 'company')->where('is_active', 1)->get();
        $clients = User::select(['id', 'name', 'email'])
            ->whereDoesntHave('roles')->where('role', 'user')->where('verified', 1)
            ->where('status', 'accepted')
            ->where('is_suspend', 0)->get();
        $specialists = User::select(['id', 'name', 'email'])->where('is_active', 1)->where('role', 'specialist')->get();

        return Inertia::render('Notifications/Create', [
            'companies' => $companies,
            'clients' => $clients,
            'specialists' => $specialists,

            'recipientTypes' => [
                ['value' => 'all', 'label' => 'All Users'],
                ['value' => 'companies', 'label' => 'Companies'],
                ['value' => 'specialists', 'label' => 'Specialist'],
                ['value' => 'clients', 'label' => 'Clients'],
            ],
        ]);
    }

    public function edit($id)
    {
        $notification = AdminNotification::findOrFail($id);

        $users = User::select(['id', 'name', 'email'])->get();
        $companies = User::select(['id', 'name', 'email'])->where('role', 'company')->where('is_active', 1)->get();
        $clients = User::select(['id', 'name', 'email'])
            ->whereDoesntHave('roles')->where('role', 'user')->where('verified', 1)
            ->where('status', 'accepted')
            ->where('is_suspend', 0)->get();
        $specialists = User::select(['id', 'name', 'email'])->where('is_active', 1)->where('role', 'specialist')->get();

        return Inertia::render('Notifications/Edit', [
            'notification' => [
                ...$notification->toArray(),
                'recipient_id' => $notification->recipient_id ? json_decode($notification->recipient_id) : [], // 🔹 تحويل JSON إلى مصفوفة
            ],
            'companies' => $companies,
            'clients' => $clients,
            'specialists' => $specialists,

            'recipientTypes' => [
                ['value' => 'all', 'label' => 'All Users'],
                ['value' => 'companies', 'label' => 'Companies'],
                ['value' => 'specialists', 'label' => 'Specialist'],
                ['value' => 'clients', 'label' => 'Clients'],
            ],
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->get('query');

        $users = User::query()
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'email']);

        return response()->json($users);
    }

    public function show(User $user)
    {
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }
}
