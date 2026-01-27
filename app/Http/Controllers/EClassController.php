<?php

namespace App\Http\Controllers;

use App\Models\EClassSession;
use App\Models\EClassParticipant;
use App\Models\SchoolClass;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class EClassController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (in_array($user->user_type, [1, 2, 3])) { // Admin or Teacher
            $sessions = EClassSession::with(['teacher', 'schoolClass', 'course'])
                ->where('teacher_id', $user->id)
                ->orWhereIn('class_id', $user->classes()->pluck('school_classes.id'))
                ->latest()
                ->paginate(15);
        } else { // Student
            $sessions = EClassSession::with(['teacher', 'schoolClass', 'course'])
                ->where('class_id', $user->class_id)
                ->orWhereNull('class_id')
                ->latest()
                ->paginate(15);
        }

        return view('eclass.index', compact('sessions'));
    }

    public function create()
    {
        $classes = SchoolClass::all();
        $courses = Course::all();

        return view('eclass.create', compact('classes', 'courses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
            'class_id'       => 'nullable|exists:school_classes,id',
            'course_id'      => 'nullable|exists:courses,id',
            'start_time'     => 'required|date|after:now',
            'duration_minutes' => 'required|integer|min:15|max:300',
            'password'       => 'nullable|string|max:50',
        ]);

        $roomName = Str::random(10) . '-' . time();

        EClassSession::create([
            'teacher_id'       => Auth::id(),
            'title'            => $request->title,
            'description'      => $request->description,
            'class_id'         => $request->class_id,
            'course_id'        => $request->course_id,
            'room_name'        => $roomName,
            'password'         => $request->password,
            'start_time'       => $request->start_time,
            'duration_minutes' => $request->duration_minutes,
            'is_active'        => 1,
        ]);

        return redirect()->route('eclass.index')->with('success', 'E-Class session created successfully.');
    }

    public function join($id)
    {
        $session = EClassSession::findOrFail($id);

        // Security: Teachers (1,2,3) and students in the class (or no class specified)
        $user = Auth::user();

        $allowed = false;
        if (in_array($user->user_type, [1, 2, 3])) {
            $allowed = true;
        } elseif ($user->user_type == 4 && ($session->class_id == $user->class_id || is_null($session->class_id))) {
            $allowed = true;
        }

        if (!$allowed) {
            abort(403);
        }

        // Record participation
        EClassParticipant::updateOrCreate(
            ['session_id' => $session->id, 'user_id' => $user->id],
            ['joined_at' => now(), 'role' => $user->user_type == 3 ? 'teacher' : 'student']
        );

        return view('eclass.join', compact('session'));
    }

    public function end($id)
    {
        $session = EClassSession::where('teacher_id', Auth::id())->findOrFail($id);
        $session->update(['is_active' => 0]);

        return redirect()->route('eclass.index')->with('success', 'Session ended.');
    }
}