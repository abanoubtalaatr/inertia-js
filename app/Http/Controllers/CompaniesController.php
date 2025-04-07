<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\Company\StoreCompanyRequest;
use App\Http\Requests\Admin\Company\UpdateCompanyRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CompaniesController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:read companies', ['only' => ['index']]);
        $this->middleware('permission:create companies', ['only' => ['create']]);
        $this->middleware('permission:update companies', ['only' => ['update', 'edit']]);
        $this->middleware('permission:delete companies', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = [
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'is_active' => $request->is_active,
        ];

        $UsersQuery = User::where('role', 'company')->where('email', '!=', 'admin@admin.com')->latest();

        $UsersQuery->when($filters['name'], function ($query, $name) {
            return $query->where('name', 'LIKE', "%{$name}%");
        });

        $UsersQuery->when($filters['email'], function ($query, $email) {
            return $query->where('email', 'LIKE', "%{$email}%");
        });
        $UsersQuery->when($filters['phone'], function ($query, $phone) {
            return $query->where('phone', 'LIKE', "%{$phone}%");
        });

        if (isset($filters['is_active'])) {
            $UsersQuery->where('is_active', $filters['is_active']);
        }
        // Paginate the filtered users
        $companies = $UsersQuery->paginate(10);

        return Inertia('Companies/index', [
            'filters' => $filters,
            'companies' => $companies,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::pluck('name')->toArray();

        return Inertia('Companies/Create', ['roles' => $roles]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCompanyRequest $request)
    {
        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => 'company',
            'avatar' => 'avatars/default_avatar.png',
            'bio' => $request->input('bio'),
            'phone' => $request->input('phone'),
        ];

        if ($request->hasFile('avatar')) {
            $userData['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user = User::create($userData);

        // Define the permissions for user management
        $userManagementPermissions = [
            'users' => ['create', 'read', 'update', 'delete', 'view'],
        ];

        // Create or get the company role
        $companyRole = Role::firstOrCreate(['name' => 'company']);

        // Generate and assign permissions
        foreach ($userManagementPermissions['users'] as $action) {
            $permissionName = "{$action} users";
            Permission::firstOrCreate(['name' => $permissionName]);
            $companyRole->givePermissionTo($permissionName);
        }

        // Assign the role to the user
        $user->assignRole($companyRole);

        $user->save();

        if ($request->has('selectedRoles')) {
            $user->syncRoles($request->selectedRoles);
        }

        return redirect()->route('companies.index')
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
    public function edit(User $company)
    {
        $roles = Role::pluck('name', 'name')->all();
        $userRoles = $company->roles->pluck('name')->all();

        return Inertia('Companies/Edit', [
            'company' => $company,
            'roles' => $roles,
            'userRoles' => $userRoles,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCompanyRequest $request, User $company)
    {
        try {
            DB::beginTransaction();

            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'bio' => $request->input('bio'),
                'phone' => $request->input('phone'),
            ];

            if ($request->filled('password')) {
                $userData['password'] = bcrypt($request->password);
            }

            if ($request->hasFile('avatar')) {
                if ($company->avatar && $company->avatar !== 'avatars/default_avatar.png') {
                    Storage::disk('public')->delete($company->avatar);
                }

                $path = $request->file('avatar')->store('avatars', 'public');
                $userData['avatar'] = $path;
            }

            // تحديث بيانات المستخدم
            $company->update($userData);

            $company->save();

            DB::commit();

            return redirect()
                ->route('companies.index')
                ->with('success', __('messages.data_updated_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', $e->getMessage());
        }
    }

    public function activate(User $company)
    {
        $company->update(
            [
                'is_active' => ($company->is_active) ? 0 : 1,
            ]
        );

        return redirect()->route('companies.index')
            ->with('success', __('messages.status_updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $company)
    {
        $company->delete();

        return redirect()->route('companies.index')
            ->with('success', __('messages.data_deleted_successfully'));
    }
}
