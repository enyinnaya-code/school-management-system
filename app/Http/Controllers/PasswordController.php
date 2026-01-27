<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    // Middleware is already applied via the route group in web.php

    public function showChangePasswordForm()
    {
        return view('auth.change-password');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = Auth::user();

        // Check current password
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        // Set the plain password - the 'hashed' cast in your User model will automatically hash it
        $user->password = $request->password;

        // Save and check the result
        if (!$user->save()) {
            return back()->withErrors(['password' => 'Failed to update password. Please try again.']);
        }

        // On success: redirect back to the same change-password page to show the success message
        return redirect()->route('password.change')
                         ->with('success', 'Password changed successfully.');
    }
}