<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use App\Models\User;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the profile settings form.
     */
    public function edit()
    {
        $user = User::with('role', 'pppoeSecrets', 'routers')->find(Auth::id());
        return view('profile.edit', compact('user'));
    }

    /**
     * Update the user's profile information.
     */
    public function updateProfile(Request $request)
    {
        $user = User::find(Auth::id());

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        try {
            User::where('id', $user->id)->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            return redirect()->route('profile.edit')
                ->with('success', 'Profile berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->route('profile.edit')
                ->with('error', 'Gagal memperbarui profile: ' . $e->getMessage());
        }
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request)
    {
        $user = User::find(Auth::id());

        $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->route('profile.edit')
                ->withErrors(['current_password' => 'Password saat ini tidak benar.']);
        }

        try {
            User::where('id', $user->id)->update([
                'password' => Hash::make($request->password),
            ]);

            return redirect()->route('profile.edit')
                ->with('success', 'Password berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->route('profile.edit')
                ->with('error', 'Gagal memperbarui password: ' . $e->getMessage());
        }
    }
}
