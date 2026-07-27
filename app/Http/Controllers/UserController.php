<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    protected array $roleOptions = [
        'super_admin'     => 'Super Admin',
        'admin_kasir'     => 'Admin Kasir',
        'admin_spp'       => 'Admin SPP',
        'admin_pj_kartu'  => 'Admin PJ Kartu',
    ];

    /**
     * Display a listing of users.
     */
    public function index()
    {
        $users = User::latest()->paginate(15);
        return view('users.index', compact('users'));
    }

    /**
     * Show form for creating new user.
     */
    public function create()
    {
        $roleOptions = $this->roleOptions;
        return view('users.create', compact('roleOptions'));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', Password::min(6)],
            'role'     => ['required', 'in:' . implode(',', array_keys($this->roleOptions))],
        ]);

        User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'role'      => $validated['role'],
        ]);

        session()->flash('success', 'User berhasil ditambahkan.');
        return redirect()->route('users.index');
    }

    /**
     * Show the edit form for a user.
     */
    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        $roleOptions = $this->roleOptions;
        return view('users.edit', compact('user', 'roleOptions'));
    }

    /**
     * Update an existing user.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'confirmed', Password::min(6)],
            'role'     => ['required', 'in:' . implode(',', array_keys($this->roleOptions))],
        ]);

        $user->update([
            'name'  => $validated['name'],
            'email' => $validated['email'],
            'role'  => $validated['role'],
        ]);

        // Update password only if provided
        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        session()->flash('success', 'User berhasil diperbarui.');
        return redirect()->route('users.index');
    }

    /**
     * Remove a user.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            session()->flash('error', 'Tidak bisa menghapus akun sendiri.');
            return redirect()->route('users.index');
        }

        $user->delete();
        session()->flash('success', 'User berhasil dihapus.');
        return redirect()->route('users.index');
    }
}
