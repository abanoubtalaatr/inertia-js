<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Notifications\UserRejectedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:read users', ['only' => ['index']]);
        $this->middleware('permission:create users', ['only' => ['create']]);
        $this->middleware('permission:update users', ['only' => ['update', 'edit']]);
        $this->middleware('permission:delete users', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $filters = [
            'name' => $request->name,
            'email' => $request->email,
            'company' => $request->company,
            'is_active' => $request->is_active,
            'status' => $request->status,
        ];
        if (Auth::user()->role == 'company') {
            $filters['company'] = Auth::id();
        }

        $UsersQuery = User::with('roles', 'company')
            ->where('role', 'user')
            ->where('email', '!=', 'admin@admin.com')
            ->latest();

        $companies = User::where('role', 'company')
            ->where('email', '!=', 'admin@admin.com')
            ->select('id', 'name')
            ->get();

        $UsersQuery->when($filters['name'], function ($query, $name) {
            return $query->where('name', 'LIKE', "%{$name}%");
        });

        $UsersQuery->when($filters['email'], function ($query, $email) {
            return $query->where('email', 'LIKE', "%{$email}%");
        });

        $UsersQuery->when($filters['company'], function ($query, $company) {
            return $query->where('company_id', $company);
        });

        $UsersQuery->when($filters['status'], function ($query, $status) {
            return $query->where('status', $status);
        });

        if (isset($filters['is_active'])) {
            $UsersQuery->where('is_active', $filters['is_active']);
        }

        $users = $UsersQuery->paginate(10);

        return Inertia('Users/index', [
            'filters' => $filters,
            'users' => $users,
            'companies' => $companies,
            'role' => Auth::user()->role,
        ]);
    }

    public function create()
    {
        $roles = Role::pluck('name')->toArray();

        $companies = User::where('role', 'company')
            ->where('email', '!=', 'admin@admin.com')->get();
        if (Auth::user()->role == 'company') {
            $companies = [];
        }

        return Inertia('Users/Create', [
            'roles' => $roles,
            'companies' => $companies,
            'role' => Auth::user()->role,
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'avatar' => 'avatars/default_avatar.png',
            'is_active' => 1,
            'role' => 'user',
            'company_id' => $request->input('company_id'),
            'status' => 'active',
            'is_verified' => 1,
        ];
        if (Auth::user()->role == 'company') {
            $userData['company_id'] = Auth::id();
        }

        if ($request->hasFile('avatar')) {
            $userData['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user = User::create($userData);

        if ($request->has('selectedRoles')) {
            $user->syncRoles($request->selectedRoles);
        }

        return redirect()->route('users.index')
            ->with('success', __('messages.data_saved_successfully'));
    }

    public function edit(User $user)
    {
        $roles = Role::pluck('name', 'name')->all();
        $userRoles = $user->roles->pluck('name')->all();
        $companies = User::where('role', 'company')
            ->where('email', '!=', 'admin@admin.com')->get();

        return Inertia('Users/Edit', [
            'user' => $user,
            'roles' => $roles,
            'userRoles' => $userRoles,
            'companies' => $companies,
            'role' => Auth::user()->role,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        try {
            DB::beginTransaction();

            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'company_id' => $request->input('company_id'),
                'status' => $request->status ?? $user->status,
                'rejection_reason' => $request->rejection_reason ?? $user->rejection_reason,
            ];

            if ($request->filled('password')) {
                $userData['password'] = bcrypt($request->password);
            }

            if ($request->hasFile('avatar')) {
                if ($user->avatar && $user->avatar !== 'avatars/default_avatar.png') {
                    Storage::disk('public')->delete($user->avatar);
                }
                $path = $request->file('avatar')->store('avatars', 'public');
                $userData['avatar'] = $path;
            }

            $user->update($userData);

            if ($request->has('selectedRoles')) {
                $user->syncRoles($request->selectedRoles);
            }

            DB::commit();

            return redirect()
                ->route('users.index')
                ->with('success', __('messages.data_updated_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', $e->getMessage());
        }
    }

    public function activate(User $user)
    {
        $user->update([
            'is_active' => ($user->is_active) ? 0 : 1,
            'status' => $user->status === 'pending' ? 'accepted' : ($user->status === 'accepted' ? 'pending' : $user->status),
        ]);

        return redirect()->route('users.index')
            ->with('success', __('messages.status_updated'));
    }

    public function accept(User $user)
    {
        $user->update([
            'status' => 'accepted',
            'is_active' => 1,
            'rejection_reason' => null,
        ]);

        return redirect()->route('users.index')
            ->with('success', __('messages.user_accepted'));
    }

    public function reject(Request $request, User $user)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:255',
        ]);

        $user->update([
            'status' => 'rejected',
            'is_active' => 0,
            'rejection_reason' => $request->rejection_reason,
        ]);

        // Send notification
        $user->notify(new UserRejectedNotification($request->rejection_reason));

        return redirect()->route('users.index')
            ->with('success', __('messages.user_rejected'));
    }

    public function destroy(User $user)
    {
        $user->logs()->delete();
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', __('messages.data_deleted_successfully'));
    }
}
