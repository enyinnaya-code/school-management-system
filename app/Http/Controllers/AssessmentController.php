<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assessment;
use App\Models\Section;
use App\Models\SchoolClass;
use App\Models\Session;
use App\Models\Term;
use App\Models\Course;

class AssessmentController extends Controller
{
    /**
     * Display a listing of the assessments.
     */
    public function index(Request $request)
    {
        $query = Assessment::with(['section', 'schoolClass', 'course', 'term'])->latest();

        if ($request->filled('filter_section')) {
            $query->where('section_id', $request->filter_section);
        }

        if ($request->filled('filter_class')) {
            $query->where('class_id', $request->filter_class);
        }

        if ($request->filled('filter_session')) {
            $query->where('session_id', $request->filter_session);
        }

        if ($request->filled('filter_term')) {
            $query->where('term_id', $request->filter_term);
        }

        if ($request->filled('filter_title')) {
            $query->where('title', 'like', '%' . $request->filter_title . '%');
        }

        if ($request->filled('filter_due_from')) {
            $query->where('due_date', '>=', $request->filter_due_from);
        }

        if ($request->filled('filter_due_to')) {
            $query->where('due_date', '<=', $request->filter_due_to);
        }

        $assessments = $query->paginate(10);

        $sections = Section::select('id', 'section_name')->get();
        $classes = SchoolClass::select('id', 'name')->get();
        $sessions = Session::select('id', 'name', 'is_current')->get();
        $terms = Term::select('id', 'name', 'is_current')->distinct()->get();

        return view('assignment_index', compact('assessments', 'sections', 'classes', 'sessions', 'terms'));
    }

    /**
     * Show the form for creating a new assessment.
     */
    public function create()
    {
        $sections = Section::all();
        return view('new_assignment', compact('sections'));
    }

    public function createTest()
    {
        return view('create_tests');
    }

    

    /**
     * Fetch classes for a section (AJAX).
     */
    public function getClasses($sectionId)
    {
        $classes = SchoolClass::where('section_id', $sectionId)->get(['id', 'name']);
        return response()->json($classes);
    }

    /**
     * Fetch sessions for a section (AJAX).
     */
    public function getSessions($sectionId)
    {
        $sessions = Session::where('section_id', $sectionId)->get(['id', 'name', 'is_current']);
        return response()->json($sessions);
    }

    /**
     * Fetch terms for a session (AJAX).
     */
    public function getTerms($sessionId)
    {
        $terms = Term::where('session_id', $sessionId)->get(['id', 'name', 'is_current']);
        return response()->json($terms);
    }

    /**
     * Fetch subjects (courses) for a class's section (AJAX).
     */
    public function getSubjects($classId)
    {
        $class = SchoolClass::findOrFail($classId);
        $subjects = Course::where('section_id', $class->section_id)
                          ->get(['id', 'course_name as name']);
        return response()->json($subjects);
    }

    /**
     * Store a newly created assessment in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date|after_or_equal:today',
            'total_marks' => 'required|integer|min:1',
            'section_id' => 'required|exists:sections,id',
            'class_id' => 'required|exists:school_classes,id',
            'course_id' => 'required|exists:courses,id',
            'session_id' => 'required|exists:school_sessions,id',
            'term_id' => 'required|exists:terms,id',
        ]);

        Assessment::create($request->all());

        return redirect()->route('assignments.create')->with('success', 'Assessment created successfully.');
    }

    /**
     * Display the specified assessment.
     */
    public function show(Assessment $assessment)
    {
        return view('assessments.show', compact('assessment'));
    }

    /**
     * Show the form for editing the specified assessment.
     */
   public function edit(Assessment $assessment)
{
    $sections = Section::all();
    $sessions = Session::where('section_id', $assessment->section_id)->get(['id', 'name']);
    $terms = Term::where('session_id', $assessment->session_id)->get(['id', 'name']);
    $classes = SchoolClass::where('section_id', $assessment->section_id)->get(['id', 'name']);
    $subjects = Course::where('section_id', $assessment->section_id)->get(['id', 'course_name as name']);

    return view('assignment_edit', compact('assessment', 'sections', 'sessions', 'terms', 'classes', 'subjects'));
}
    /**
     * Update the specified assessment in storage.
     */
    public function update(Request $request, Assessment $assessment)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'total_marks' => 'required|integer|min:1',
        ]);

        $assessment->update($request->all());

        return redirect()->route('assignments.index')->with('success', 'Assessment updated successfully.');
    }

    /**
     * Remove the specified assessment from storage.
     */
    public function destroy(Assessment $assessment)
    {
        $assessment->delete();

        return redirect()->route('assignments.index')->with('success', 'Assessment deleted successfully.');
    }
}