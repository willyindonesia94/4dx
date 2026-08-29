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
        $search = $request->query('search');
        $levels = ['UID', 'UP3', 'UP2K', 'UP2D', 'ULP'];
        if (auth()->user()->hasRole('Super Admin')) {
            array_unshift($levels, 'Super Admin');
        }

        $query = User::with('unit');
        
        if (!auth()->user()->hasRole('Super Admin')) {
            $query->where('role_name', '!=', 'Super Admin');
        }
        
        if ($selectedLevel) {
            if ($selectedLevel === 'Super Admin') {
                $query->where('role_name', 'Super Admin');
            } else {
                $query->where('role_name', 'LIKE', '%' . $selectedLevel . '%');
            }
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $perPage = $request->query('per_page', 20);
        $users = $query->paginate($perPage)->withQueryString();
        
        return view('users.index', compact('users', 'levels', 'selectedLevel', 'search', 'perPage'));
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
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role_name' => ['required', 'exists:roles,name'],
            'unit_id' => ['required', 'exists:master_units,id'],
            'matrix_group_id' => ['required', 'string'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role_name' => $request->role_name,
            'unit_id' => $request->unit_id,
            'matrix_group_id' => $request->matrix_group_id,
        ]);

        $user->assignRole($request->role_name);

        $level = $request->input('return_level');
        $params = $level ? ['level' => $level] : [];

        return redirect()->route('users.index', $params)->with('success', 'Pengguna berhasil ditambahkan.');
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
            'username' => ['required', 'string', 'max:255', 'unique:users,username,'.$user->id],
            'role_name' => ['required', 'exists:roles,name'],
            'unit_id' => ['required', 'exists:master_units,id'],
            'matrix_group_id' => ['required', 'string'],
        ]);

        $user->update([
            'name' => $request->name,
            'username' => $request->username,
            'role_name' => $request->role_name,
            'unit_id' => $request->unit_id,
            'matrix_group_id' => $request->matrix_group_id,
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => ['confirmed', Rules\Password::defaults()]]);
            $user->update(['password' => Hash::make($request->password)]);
        }

        $user->syncRoles([$request->role_name]);

        $level = $request->input('return_level');
        $params = $level ? ['level' => $level] : [];

        return redirect()->route('users.index', $params)->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $level = request()->query('level');
        $params = $level ? ['level' => $level] : [];

        if ($user->id === auth()->id()) {
            return redirect()->route('users.index', $params)->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();
        return redirect()->route('users.index', $params)->with('success', 'Pengguna berhasil dihapus.');
    }

    public function previewImport(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120'
        ]);

        try {
            $data = \Maatwebsite\Excel\Facades\Excel::toArray(new \App\Imports\UsersPreviewImport, $request->file('file'));
            // toArray returns an array of sheets. We assume the first sheet is the one we want.
            $rows = $data[0] ?? [];
            
            // Filter out empty rows
            $rows = array_filter($rows, function($row) {
                return !empty($row['nama']) && !empty($row['username']) && !empty($row['role_name']);
            });

            return response()->json([
                'success' => true,
                'data' => array_values($rows)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membaca file: ' . $e->getMessage()
            ], 400);
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120'
        ]);

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\UsersImport, $request->file('file'));
            \Illuminate\Support\Facades\DB::commit();
            return redirect()->route('users.index')->with('success', 'Data pengguna berhasil diunggah secara massal.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return redirect()->route('users.index')->with('error', 'Gagal mengunggah data: ' . $e->getMessage());
        }
    }

    public function template()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\UsersTemplateExport, 'Template_Bulk_User.xlsx');
    }
}
