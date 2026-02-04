<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class LoginController extends Controller
{
    // Show the login form
    public function showLoginForm()
    {
        return view('index');
    }

    public function login(Request $request)
    {
        // Validate the form data
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'user_type' => 'required|in:admin,staff,student,parent',
        ]);

        // Map the selected user_type (string) to allowed numeric values in DB
        $selectedType = $request->user_type;
        $allowedUserTypes = [];

        switch ($selectedType) {
            case 'admin':
                $allowedUserTypes = [1, 2]; // Superadmin and Admin
                break;
            case 'staff':
                $allowedUserTypes = [3, 6, 7, 8, 9, 10];
                break;
            case 'student':
                $allowedUserTypes = [4]; // Student only
                break;
            case 'parent':
                $allowedUserTypes = [5]; // Parent only
                break;
        }

        // Get the user by email
        $user = User::where('email', $request->email)->first();

        // Debug logging
        Log::info('Login attempt', [
            'email' => $request->email,
            'selected_user_type' => $selectedType,
            'allowed_db_types' => $allowedUserTypes,
            'user_found' => $user ? 'yes' : 'no',
            'user_type_in_db' => $user ? $user->user_type : 'N/A',
            'is_active' => $user ? $user->is_active : 'N/A',
        ]);

        // Check if user exists
        if (!$user) {
            Log::warning('User not found', ['email' => $request->email]);
            return back()->with('error', 'Invalid email or password.');
        }

        // Check if the user's type is allowed for the selected role
        if (!in_array($user->user_type, $allowedUserTypes)) {
            Log::warning('User type mismatch', [
                'email' => $request->email,
                'db_user_type' => $user->user_type,
                'selected_type' => $selectedType,
                'allowed' => $allowedUserTypes
            ]);
            return back()->with('error', 'Invalid credentials for selected user type.');
        }

        // Check if account is suspended
        if ($user->is_active == 0) {
            return back()->with('error', 'Your account is suspended.');
        }

        // Check login attempts
        if ($user->login_attempts <= 0) {
            return back()->with('error', 'Your account is suspended due to multiple failed login attempts.');
        }

        // Attempt authentication
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            Log::info('Login successful', ['email' => $request->email, 'user_type' => $user->user_type]);

            // Reset login attempts
            $user->update(['login_attempts' => 3]);
            $request->session()->regenerate();

            // Redirect based on actual user_type in database
            switch ($user->user_type) {
                case 1: // superAdmin
                case 2: // Admin
                case 7: // principal
                case 8: // viceprincipal
                case 9: // dean of studies
                case 10: // Guidance counsellor
                    return redirect()->route('admins.dashboard');
                case 3: // Teacher
                    return redirect()->route('teachers.dashboard');
                case 4: // Student
                    return redirect()->route('students.dashboard');
                case 5: // Parent
                    return redirect()->route('parents.dashboard');
                case 6: // Accountant
                    return redirect()->route('bursar.dashboard');
                default:
                    return redirect()->route('dashboard');
            }
        } else {
            Log::warning('Password incorrect', ['email' => $request->email]);

            // Decrement attempts
            $user->decrement('login_attempts');

            if ($user->login_attempts <= 0) {
                $user->update(['is_active' => 0]);
                return back()->with('error', 'Your account is suspended due to multiple failed login attempts.');
            }

            return back()->with('error', 'Incorrect password. You have ' . $user->login_attempts . ' remaining attempts.');
        }
    }

    // Logout user
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
