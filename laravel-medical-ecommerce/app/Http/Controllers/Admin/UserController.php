<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Conversation;
use App\Models\Order;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->latest()->paginate(15);
        
        // Roles might not be seeded yet, fallback graciously or fetch them
        try {
            $roles = Role::all();
        } catch (\Exception $e) {
            $roles = collect([]);
        }

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request, true);
        $role = $data['role'];
        unset($data['role']);

        $user = User::create($data);
        $user->assignRole($role);

        return redirect()->route('admin.users.show', $user->id)
            ->with('success', __('admin.user_created'));
    }

    public function show($id)
    {
        $user = User::with(['roles', 'orders'])->findOrFail($id);
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $this->validatedData($request, false, $user);
        $role = $data['role'];
        unset($data['role']);

        if ($data['password'] === null || $data['password'] === '') {
            unset($data['password']);
        }

        $user->update($data);
        $user->syncRoles([$role]);

        return redirect()->route('admin.users.show', $user->id)
            ->with('success', __('admin.user_updated'));
    }

    public function destroy(User $user)
    {
        if ((int) auth()->id() === (int) $user->id) {
            return back()->with('error', __('admin.cannot_delete_current_user'));
        }

        $hasLinkedData = Order::where('user_id', $user->id)->exists()
            || Patient::where('user_id', $user->id)->exists()
            || Consultation::where(fn ($query) => $query
                ->where('user_id', $user->id)
                ->orWhere('doctor_id', $user->id))
                ->exists()
            || Conversation::where(fn ($query) => $query
                ->where('user_id', $user->id)
                ->orWhere('doctor_id', $user->id))
                ->exists()
            || Appointment::where('doctor_id', $user->id)->exists();

        if ($hasLinkedData) {
            return back()->with('error', __('admin.user_delete_blocked'));
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', __('admin.user_deleted'));
    }

    public function assignRole(Request $request, $id)
    {
        $request->validate([
            'role' => ['required', 'string', Rule::in(['user', 'doctor', 'admin', 'delivery'])],
        ]);

        $user = User::findOrFail($id);

        try {
            // First time role creation if missing in Spatie table
            $role = Role::firstOrCreate(['name' => $request->role, 'guard_name' => 'web']);
            $user->syncRoles([$role->name]);
            return back()->with('success', __('admin.user_role_updated'));
        } catch (\Exception $e) {
            report($e);

            return back()->with('error', __('admin.unexpected_error'));
        }
    }

    private function validatedData(Request $request, bool $creating, ?User $user = null): array
    {
        $passwordRules = $creating
            ? ['required', 'string', 'min:6']
            : ['nullable', 'string', 'min:6'];

        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => [
                'required',
                'string',
                'max:30',
                Rule::unique('users', 'phone')->ignore($user?->id),
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'password' => $passwordRules,
            'role' => ['required', 'string', Rule::in(['user', 'doctor', 'admin', 'delivery'])],
        ]);
    }
}
