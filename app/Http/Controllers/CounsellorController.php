<?php

namespace App\Http\Controllers;

use App\Models\CounsellingSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CounsellorController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    //     // Optional: Create a policy later for finer control
    // }

    public function index()
    {
        $sessions = CounsellingSession::where('counsellor_id', Auth::id())
            ->with('student')
            ->orderByDesc('session_date')
            ->paginate(15);

        return view('counsellor.index', compact('sessions'));
    }

    public function create()
    {
        $students = User::where('user_type', 4)
            ->orderBy('name')
            ->pluck('name', 'id'); // Better for <select>

        return view('counsellor.create', compact('students'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id,user_type,4',
            'session_date' => 'required|date|after_or_equal:today',
            'session_time' => 'nullable|date_format:H:i',
            'reason' => 'required|string|max:1000',
        ]);

        CounsellingSession::create([
            'student_id' => $request->student_id,
            'counsellor_id' => Auth::id(),
            'session_date' => $request->session_date,
            'session_time' => $request->session_time,
            'reason' => $request->reason,
            'status' => 'scheduled',
        ]);

        return redirect()->route('counsellor.index')
            ->with('success', 'Counselling session scheduled successfully.');
    }

    public function show(CounsellingSession $session)
    {
        $this->authorizeSession($session);

        return view('counsellor.show', compact('session'));
    }

    public function edit(CounsellingSession $session)
    {
        $this->authorizeSession($session);

        $students = User::where('user_type', 4)
            ->orderBy('name')
            ->pluck('name', 'id');

        return view('counsellor.edit', compact('session', 'students'));
    }

    public function update(Request $request, CounsellingSession $session)
    {
        $this->authorizeSession($session);

        $request->validate([
            'session_date' => 'required|date',
            'session_time' => 'nullable|date_format:H:i',
            'reason' => 'required|string|max:1000',
            'notes' => 'nullable|string',
            'status' => 'required|in:scheduled,completed,cancelled,no_show',
            'follow_up_date' => 'nullable|date|after_or_equal:session_date',
            'follow_up_notes' => 'nullable|string',
        ]);

        // Prevent mass assignment of protected fields
        $session->update($request->only([
            'session_date',
            'session_time',
            'reason',
            'notes',
            'status',
            'follow_up_date',
            'follow_up_notes',
        ]));

        return redirect()->route('counsellor.index')
            ->with('success', 'Session updated successfully.');
    }

    // Optional: Soft delete or cancel session
    public function destroy(CounsellingSession $session)
    {
        $this->authorizeSession($session);

        $session->delete();

        return redirect()->route('counsellor.index')
            ->with('success', 'Session deleted successfully.');
    }

    // Helper method to avoid repeating auth check
    private function authorizeSession(CounsellingSession $session)
    {
        if ($session->counsellor_id !== Auth::id()) {
            abort(403, 'Unauthorized access.');
        }
    }
}