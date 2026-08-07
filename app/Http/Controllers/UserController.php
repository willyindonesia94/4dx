<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\MasterUnit;
use App\Models\MasterBidang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $selectedLevel = $request->query('level');
        $levels = ['Super Admin', 'UID', 'UP3', 'ULP'];

        $query = User::with('unit');
        
        if ($selectedLevel) {
            if ($selectedLevel === 'Super Admin') {
                $query->where('role_name', 'Super Admin');
            } else {
                $query->where('role_name', 'LIKE', '%' . $selectedLevel . '%');
            }
        }

        $users = $query->paginate(20)->appends($request->query());
        
        return view('users.index', compact('users', 'levels', 'selectedLevel'));
    }

    public function create()
    {
        $roles = Role::all();
        $units = MasterUnit::all();
        $bidangs = MasterBidang::orderBy('name', 'asc')->get();
        
        return view('users.create', compact('roles', 'units', 'bidangs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role_name' => ['required', 'exists:roles,name'],
            'unit_id' => ['required', 'exists:master_units,id'],
            'matrix_group_id' => ['required', 'string'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_name' => $request->role_name,
            'unit_id' => $request->unit_id,
            'matrix_group_id' => $request->matrix_group_id,
        ]);

        $user->assignRole($request->role_name);

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $units = MasterUnit::all();
        $bidangs = MasterBidang::orderBy('name', 'asc')->get();
        
        return view('users.edit', compact('user', 'roles', 'units', 'bidangs'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role_name' => ['required', 'exists:roles,name'],
            'unit_id' => ['required', 'exists:master_units,id'],
            'matrix_group_id' => ['required', 'string'],
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role_name' => $request->role_name,
            'unit_id' => $request->unit_id,
            'matrix_group_id' => $request->matrix_group_id,
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => ['confirmed', Rules\Password::defaults()]]);
            $user->update(['password' => Hash::make($request->password)]);
        }

        $user->syncRoles([$request->role_name]);

        return redirect()->route('users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}
