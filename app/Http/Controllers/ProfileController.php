<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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
            'pic_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'isp_logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'company_name' => 'nullable|string|max:255',
            'company_address' => 'nullable|string|max:1000',
            'company_phone' => 'nullable|string|max:20',
        ]);

        try {
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'company_name' => $request->company_name,
                'company_address' => $request->company_address,
                'company_phone' => $request->company_phone,
            ];

            // Handle PIC photo upload
            if ($request->hasFile('pic_photo')) {
                // Delete old photo if exists
                if ($user->pic_photo && Storage::disk('public')->exists($user->pic_photo)) {
                    Storage::disk('public')->delete($user->pic_photo);
                }
                
                $picPath = $request->file('pic_photo')->store('profile/pics', 'public');
                $updateData['pic_photo'] = $picPath;
            }

            // Handle ISP logo upload
            if ($request->hasFile('isp_logo')) {
                // Delete old logo if exists
                if ($user->isp_logo && Storage::disk('public')->exists($user->isp_logo)) {
                    Storage::disk('public')->delete($user->isp_logo);
                }
                
                $logoPath = $request->file('isp_logo')->store('profile/logos', 'public');
                $updateData['isp_logo'] = $logoPath;
            }

            User::where('id', $user->id)->update($updateData);

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

    /**
     * Delete PIC photo
     */
    public function deletePicPhoto()
    {
        $user = User::find(Auth::id());
        
        try {
            if ($user->pic_photo && Storage::disk('public')->exists($user->pic_photo)) {
                Storage::disk('public')->delete($user->pic_photo);
            }
            
            $user->update(['pic_photo' => null]);
            
            return response()->json(['success' => true, 'message' => 'Foto PIC berhasil dihapus.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus foto: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete ISP logo
     */
    public function deleteIspLogo()
    {
        $user = User::find(Auth::id());
        
        try {
            if ($user->isp_logo && Storage::disk('public')->exists($user->isp_logo)) {
                Storage::disk('public')->delete($user->isp_logo);
            }
            
            $user->update(['isp_logo' => null]);
            
            return response()->json(['success' => true, 'message' => 'Logo ISP berhasil dihapus.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus logo: ' . $e->getMessage()]);
        }
    }
}
