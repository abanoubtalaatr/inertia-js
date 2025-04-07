<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\Admin\StoreAdminRequest;
use App\Http\Requests\Admin\Admin\UpdateAdminRequest;
use App\Models\RoleTranslation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class AdminsController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:read admins', ['only' => ['index']]);
        $this->middleware('permission:create admins', ['only' => ['create']]);
        $this->middleware('permission:update admins', ['only' => ['update', 'edit']]);
        $this->middleware('permission:delete admins', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        // Define the filters
        $filters = [
            'name' => $request->name,
            'email' => $request->email,
            'is_active' => $request->is_active,
        ];

        $UsersQuery = User::where('role', 'admin')->whereHas('roles', function ($q) {
            $q->where('name', '!=', 'superadmin');
        })->latest();

        $UsersQuery->when($filters['name'], function ($query, $name) {
            return $query->where('name', 'LIKE', "%{$name}%");
        });

        $UsersQuery->when($filters['email'], function ($query, $email) {

            return $query->where('email', 'LIKE', "%{$email}%");
        });

        if (isset($filters['is_active'])) {
            $UsersQuery->where('is_active', $filters['is_active']);
        }
        // Paginate the filtered users
        $admins = $UsersQuery->paginate(10);

        return Inertia('Admins/index', [
            'filters' => $filters,
            'admins' => $admins,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Fetch all roles except 'superadmin'
        $roles = Role::where('name', '!=', 'superadmin')->get();

        // Get all relevant translations in one query
        $roleIds = $roles->pluck('id')->toArray();
        $translations = RoleTranslation::where('locale', app()->getLocale())
            ->whereIn('role_id', $roleIds)
            ->pluck('name', 'role_id')
            ->toArray();

        // Map the roles to include both id and translated name
        $roleData = $roles->map(function ($role) use ($translations) {
            return [
                'id' => $role->id,
                'name' => $translations[$role->id] ?? $role->name, // Use translated name or fallback to default
            ];
        })->toArray();

        return Inertia('Admins/Create', ['roles' => $roleData]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAdminRequest $request)
    {

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => 'admin',
            'avatar' => 'avatars/default_avatar.png', // القيمة الافتراضية،
        ];

        if ($request->hasFile('avatar')) {
            $userData['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user = User::create($userData);

        $user->save();

        if ($request->has('selectedRoles')) {
            $user->syncRoles($request->selectedRoles);
        }

        return redirect()->route('admins.index')
            ->with('success', __('messages.data_saved_successfully'));
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $admin)
    {
        // Fetch all roles except 'superadmin'
        $roles = Role::where('name', '!=', 'superadmin')->get();

        // Get all relevant translations in one query
        $roleIds = $roles->pluck('id')->toArray();
        $translations = RoleTranslation::where('locale', app()->getLocale())
            ->whereIn('role_id', $roleIds)
            ->pluck('name', 'role_id')
            ->toArray();

        // Map the roles to include both id and translated name
        $roleData = $roles->map(function ($role) use ($translations) {
            return [
                'id' => $role->id,
                'name' => $translations[$role->id] ?? $role->name, // Use translated name or fallback
            ];
        })->toArray();

        // Get the user's current role IDs
        $userRoles = $admin->roles->pluck('id')->toArray();

        return Inertia('Admins/Edit', [
            'user' => $admin,
            'roles' => $roleData,
            'userRoles' => $userRoles,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAdminRequest $request, User $admin)
    {

        try {
            DB::beginTransaction();

            $userData = [
                'name' => $request->name,
                'email' => $request->email,
            ];

            if ($request->filled('password')) {
                $userData['password'] = bcrypt($request->password);
            }

            if ($request->hasFile('avatar')) {
                if ($admin->avatar && $admin->avatar !== 'avatars/default_avatar.png') {
                    Storage::disk('public')->delete($admin->avatar);
                }

                $path = $request->file('avatar')->store('avatars', 'public');
                $userData['avatar'] = $path;

                Log::info('Image uploaded:', ['path' => $path]);
            }

            Log::info('Updating user with data:', $userData);

            // تحديث بيانات المستخدم
            $admin->update($userData);

            $admin->save();

            // تحديث الأدوار
            if ($request->has('selectedRoles')) {
                $admin->syncRoles($request->selectedRoles);
            }

            DB::commit();

            return redirect()
                ->route('admins.index')
                ->with('success', __('messages.data_updated_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('User Update Error: '.$e->getMessage());
            Log::error('Stack trace: '.$e->getTraceAsString());

            return back()->with('error', $e->getMessage());
        }
    }

    public function activate(User $admin)
    {
        $admin->update(
            [
                'is_active' => ($admin->is_active) ? 0 : 1,
            ]
        );

        return redirect()->route('admins.index')
            ->with('success', __('messages.status_updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $admin)
    {
        $admin->delete();

        return redirect()->route('admins.index')
            ->with('success', __('messages.data_deleted_successfully'));
    }
}
