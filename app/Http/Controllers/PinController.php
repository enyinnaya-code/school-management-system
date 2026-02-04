<?php

namespace App\Http\Controllers;

use App\Models\Pin;
use App\Models\Section;
use App\Models\Session;
use App\Models\Term;
use App\Models\IssuedPin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PinController extends Controller
{
    public function index(Request $request)
    {
        $query = Pin::with(['section', 'session', 'term', 'createdBy', 'issuedPin.student', 'issuedPin.schoolClass']);

        // Apply filters
        if ($request->filter_section) {
            $query->where('section_id', $request->filter_section);
        }
        if ($request->filter_session) {
            $query->where('session_id', $request->filter_session);
        }
        if ($request->filter_term) {
            $query->where('term_id', $request->filter_term);
        }
        if ($request->filter_status !== null) {
            $query->where('is_used', $request->filter_status);
        }

        // NEW: Filter by issued status
        if ($request->filter_issued !== null) {
            if ($request->filter_issued == '1') {
                $query->whereHas('issuedPin');
            } else {
                $query->whereDoesntHave('issuedPin');
            }
        }

        $pins = $query->orderBy('created_at', 'desc')->paginate(10);
        $sections = Section::all();
        $sessions = Session::all();
        $terms = Term::all();

        return view('pins_index', compact('pins', 'sections', 'sessions', 'terms'));
    }

    public function showDetails($id)
    {
        $pin = Pin::with(['section', 'session', 'term', 'createdBy', 'issuedPin.student', 'issuedPin.schoolClass', 'issuedPin.issuedBy'])
            ->findOrFail($id);

        return response()->json([
            'pin_code' => $pin->pin_code,
            'section' => $pin->section->section_name,
            'session' => $pin->session->name,
            'term' => $pin->term->name,
            'is_used' => $pin->is_used,
            'usage_count' => $pin->usage_count,
            'created_by' => $pin->createdBy->name,
            'created_at' => $pin->created_at->format('M d, Y h:i A'),
            'is_issued' => $pin->issuedPin ? true : false,
            'issued_to' => $pin->issuedPin ? [
                'student_name' => $pin->issuedPin->student->name,
                'admission_no' => $pin->issuedPin->student->admission_no,
                'email' => $pin->issuedPin->student->email,
                'class' => $pin->issuedPin->schoolClass->name,
                'issued_by' => $pin->issuedPin->issuedBy->name,
                'issued_at' => $pin->issuedPin->created_at->format('M d, Y h:i A')
            ] : null
        ]);
    }


    public function create()
    {
        $sections = Section::all();
        $sessions = collect([]);
        $terms = collect([]);
        $current_session_id = null;
        $current_term_id = null;

        return view('create_pin', compact('sections', 'sessions', 'terms', 'current_session_id', 'current_term_id'));
    }

    public function getSessions($sectionId)
    {
        $sessions = Session::where('section_id', $sectionId)
            ->select('id', 'name', 'is_current')
            ->get();

        $currentSession = $sessions->firstWhere('is_current', true);

        return response()->json([
            'sessions' => $sessions,
            'current_session_id' => $currentSession ? $currentSession->id : null
        ]);
    }

    public function getTerms($sessionId)
    {
        $terms = Term::where('session_id', $sessionId)
            ->select('id', 'name', 'is_current')
            ->get();

        $currentTerm = $terms->firstWhere('is_current', true);

        return response()->json([
            'terms' => $terms,
            'current_term_id' => $currentTerm ? $currentTerm->id : null
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'section_id' => 'required|exists:sections,id',
            'session_id' => 'required|exists:school_sessions,id',
            'term_id' => 'required|exists:terms,id',
            'num_pins' => 'required|integer|min:1|max:100',
        ]);

        $session = Session::where('id', $request->session_id)
            ->where('section_id', $request->section_id)
            ->firstOrFail();

        $term = Term::where('id', $request->term_id)
            ->where('session_id', $request->session_id)
            ->firstOrFail();

        $numPins = $request->num_pins;
        $generatedPins = [];

        for ($i = 0; $i < $numPins; $i++) {
            do {
                $pinCode = Str::upper(Str::random(12));
            } while (Pin::where('pin_code', $pinCode)->exists());

            $pin = Pin::create([
                'section_id' => $request->section_id,
                'session_id' => $request->session_id,
                'term_id' => $request->term_id,
                'pin_code' => $pinCode,
                'is_used' => false,
                'usage_count' => 0, // Initialize usage_count
                'created_by' => Auth::id(),
            ]);

            $generatedPins[] = $pin;
        }

        return redirect()->route('pins.index')->with('success', 'Pins generated successfully.');
    }

    public function resetUsage(Request $request, $id)
    {
        // Restrict to user_type = 1
        if (Auth::user()->user_type !== 1) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $pin = Pin::findOrFail($id);
        $pin->update([
            'is_used' => false,
            'usage_count' => 0,
        ]);

        return redirect()->route('pins.index')->with('success', 'Pin usage reset successfully.');
    }


    public function issueForm()
    {
        $sections = Section::all();
        $classes = collect([]);
        $sessions = collect([]);
        $terms = collect([]);

        return view('issue_pins', compact('sections', 'classes', 'sessions', 'terms'));
    }

    public function getStudents($sectionId, $classId)
    {
        $students = User::where('user_type', 4) // Changed from 3 to 4
            ->where('class_id', $classId)
            ->whereHas('class', function ($query) use ($sectionId) {
                $query->where('section_id', $sectionId);
            })
            ->select('id', 'name', 'admission_no')
            ->orderBy('name')
            ->get();

        return response()->json(['students' => $students]);
    }

    public function issueToStudents(Request $request)
    {
        $request->validate([
            'section_id' => 'required|exists:sections,id',
            'class_id' => 'required|exists:school_classes,id',
            'session_id' => 'required|exists:school_sessions,id',
            'term_id' => 'required|exists:terms,id',
            'students' => 'required|array|min:1',
            'students.*' => 'exists:users,id'
        ]);

        $studentIds = $request->students;
        $numStudents = count($studentIds);

        // Check for available unissued pins for this section/session/term
        $availablePins = Pin::where('section_id', $request->section_id)
            ->where('session_id', $request->session_id)
            ->where('term_id', $request->term_id)
            ->where('is_used', false)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('issued_pins')
                    ->whereColumn('issued_pins.pin_id', 'pins.id');
            })
            ->limit($numStudents)
            ->get();

        if ($availablePins->count() < $numStudents) {
            return redirect()->back()->with(
                'error',
                "Insufficient PINs available. You need {$numStudents} PINs but only {$availablePins->count()} are available for this section/session/term."
            );
        }

        // Check if any students already have PINs issued for this class/session/term
        $alreadyIssued = IssuedPin::whereIn('student_id', $studentIds)
            ->where('class_id', $request->class_id)
            ->where('session_id', $request->session_id)
            ->where('term_id', $request->term_id)
            ->with('student')
            ->get();

        if ($alreadyIssued->count() > 0) {
            $studentNames = $alreadyIssued->pluck('student.name')->implode(', ');
            return redirect()->back()->with(
                'error',
                "The following students already have PINs issued for this term: {$studentNames}"
            );
        }

        // Issue PINs to students
        DB::beginTransaction();
        try {
            foreach ($studentIds as $index => $studentId) {
                $pin = $availablePins[$index];

                IssuedPin::create([
                    'pin_id' => $pin->id,
                    'student_id' => $studentId,
                    'section_id' => $request->section_id,
                    'class_id' => $request->class_id,
                    'session_id' => $request->session_id,
                    'term_id' => $request->term_id,
                    'issued_by' => Auth::id()
                ]);
            }

            DB::commit();
            return redirect()->route('pins.index')->with(
                'success',
                "Successfully issued {$numStudents} PINs to students."
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'An error occurred while issuing PINs: ' . $e->getMessage());
        }
    }

    public function printIssuedPins(Request $request)
    {
        $query = IssuedPin::with(['pin', 'student', 'schoolClass', 'session', 'term']);

        // Apply filters if provided
        if ($request->section_id) {
            $query->where('section_id', $request->section_id);
        }
        if ($request->class_id) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->session_id) {
            $query->where('session_id', $request->session_id);
        }
        if ($request->term_id) {
            $query->where('term_id', $request->term_id);
        }

        $issuedPins = $query->orderBy('created_at', 'desc')->get();

        if ($issuedPins->isEmpty()) {
            return redirect()->back()->with('error', 'No issued PINs found matching your criteria.');
        }

        return view('print_issued_pins', compact('issuedPins'));
    }
}