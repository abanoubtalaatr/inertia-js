<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\Specialist\StoreSpecialistRequest;
use App\Http\Requests\Admin\Specialist\UpdateSpecialistRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class SpecialistsController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:read specialists', ['only' => ['index']]);
        $this->middleware('permission:create specialists', ['only' => ['create']]);
        $this->middleware('permission:update specialists', ['only' => ['update', 'edit']]);
        $this->middleware('permission:delete specialists', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'bio' => $request->bio,
            'is_active' => $request->is_active,
        ];

        $specialistsQuery = User::with('roles', 'company')
            ->where('role', 'specialist')
            ->where('email', '!=', 'admin@admin.com')
            ->latest();

        $specialistsQuery->when($filters['name'], function ($query, $name) {
            return $query->where('name', 'LIKE', "%{$name}%");
        });

        $specialistsQuery->when($filters['email'], function ($query, $email) {
            return $query->where('email', 'LIKE', "%{$email}%");
        });

        $specialistsQuery->when($filters['bio'], function ($query, $bio) {
            return $query->where('bio', 'LIKE', "%{$bio}%");
        });

        $specialistsQuery->when($filters['phone'], function ($query, $phone) {
            return $query->where('phone', 'LIKE', "%{$phone}%");
        });

        if (isset($filters['is_active'])) {
            $specialistsQuery->where('is_active', $filters['is_active']);
        }

        $specialists = $specialistsQuery->paginate(10);

        return Inertia('Specialists/index', [
            'filters' => $filters,
            'specialists' => $specialists,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::pluck('name')->toArray();

        return Inertia('Specialists/Create', [
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSpecialistRequest $request)
    {
        $specialistData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'avatar' => 'avatars/default_avatar.png',
            'is_active' => 1,
            'role' => 'specialist',
            'bio' => $request->bio,
            'phone' => $request->phone,
        ];

        if ($request->hasFile('avatar')) {
            $specialistData['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $specialist = User::create($specialistData);

        $specialist->save();

        if ($request->has('selectedRoles')) {
            $specialist->syncRoles($request->selectedRoles);
        }

        return redirect()->route('specialists.index')
            ->with('success', __('messages.data_saved_successfully'));
    }

    /**
     * Display the specified resource.
     */
    public function show(User $specialist)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $specialist)
    {

        $roles = Role::pluck('name', 'name')->all();
        $specialistRoles = $specialist->roles->pluck('name')->all();

        return Inertia('Specialists/Edit', [
            'specialist' => $specialist,
            'roles' => $roles,
            'userRoles' => $specialistRoles,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSpecialistRequest $request, User $specialist)
    {

        try {
            DB::beginTransaction();

            $specialistData = [
                'name' => $request->name,
                'email' => $request->email,
                'bio' => $request->bio,
                'phone' => $request->phone,
            ];

            if ($request->filled('password')) {
                $specialistData['password'] = bcrypt($request->password);
            }

            if ($request->hasFile('avatar')) {
                if ($specialist->avatar && $specialist->avatar !== 'avatars/default_avatar.png') {
                    Storage::disk('public')->delete($specialist->avatar);
                }

                $path = $request->file('avatar')->store('avatars', 'public');
                $specialistData['avatar'] = $path;
            }

            // تحديث بيانات المستخدم
            $specialist->update($specialistData);

            $specialist->save();

            DB::commit();

            return redirect()
                ->route('specialists.index')
                ->with('success', __('messages.data_updated_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', $e->getMessage());
        }
    }

    public function activate(User $specialist)
    {
        $specialist->update(
            [
                'is_active' => ($specialist->is_active) ? 0 : 1,
            ]
        );

        return redirect()->route('specialists.index')
            ->with('success', __('messages.status_updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $specialist)
    {
        $specialist->logs()->delete();

        $specialist->delete();

        return redirect()->route('specialists.index')
            ->with('success', __('messages.data_deleted_successfully'));
    }
}
