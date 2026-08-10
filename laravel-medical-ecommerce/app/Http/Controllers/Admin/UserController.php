<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

    public function show($id)
    {
        $user = User::with(['roles', 'orders'])->findOrFail($id);
        return view('admin.users.show', compact('user'));
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
            return back()->with('success', 'User role updated successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Error assigning role: ' . $e->getMessage());
        }
    }
}
