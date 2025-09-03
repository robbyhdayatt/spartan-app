<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Jabatan;
use App\Models\Gudang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['jabatan', 'gudang'])->latest()->get();
        $jabatans = Jabatan::where('is_active', true)->orderBy('nama_jabatan')->get();
        $gudangs = Gudang::where('is_active', true)->orderBy('nama_gudang')->get();
        return view('admin.users.index', compact('users', 'jabatans', 'gudangs'));
    }

    public function store(Request $request)
    {
        $this->authorize('is-super-admin');
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nik' => 'required|string|max:50|unique:users',
            'username' => 'required|string|max:100|unique:users',
            'jabatan_id' => 'required|exists:jabatans,id',
            'gudang_id' => 'nullable|exists:gudangs,id',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'nama' => $validated['nama'],
            'nik' => $validated['nik'],
            'username' => $validated['username'],
            'jabatan_id' => $validated['jabatan_id'],
            'gudang_id' => $validated['gudang_id'],
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil ditambahkan!');
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('is-super-admin');
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nik' => 'required|string|max:50|unique:users,nik,' . $user->id,
            'username' => 'required|string|max:100|unique:users,username,' . $user->id,
            'jabatan_id' => 'required|exists:jabatans,id',
            'gudang_id' => 'nullable|exists:gudangs,id',
            'password' => 'nullable|string|min:8|confirmed',
            'is_active' => 'required|boolean',
        ]);

        // Hanya update password jika diisi
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        // Hapus password dari array agar tidak ter-update sebagai null
        unset($validated['password']);
        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil diperbarui!');
    }

    public function destroy(User $user)
    {
        $this->authorize('is-super-admin');
        // Pencegahan agar tidak bisa menghapus diri sendiri
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil dihapus!');
    }
}
