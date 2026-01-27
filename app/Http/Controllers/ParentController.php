<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Section;
use App\Models\Term;
use App\Models\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\FeeProspectus;
use App\Models\Payment;

class ParentController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('user_type', 5)->with('students');

        if ($request->filled('filter_name')) {
            $query->where('name', 'like', '%' . $request->filter_name . '%');
        }

        if ($request->filled('filter_email')) {
            $query->where('email', 'like', '%' . $request->filter_email . '%');
        }

        if ($request->filled('filter_date_from')) {
            $query->where('created_at', '>=', $request->filter_date_from);
        }

        if ($request->filled('filter_date_to')) {
            $query->where('created_at', '<=', $request->filter_date_to);
        }

        $parents = $query->paginate(20);

        return view('manage_parents', compact('parents'));
    }

    public function create()
    {
        return view('add_parent');
    }

    /**
     * Search students globally.
     */
    public function searchStudents(Request $request)
    {
        $q = $request->get('q');
        $page = $request->get('page', 1);

        $students = User::where('user_type', 4)
            ->when($q, function ($query, $q) {
                return $query->where('name', 'like', '%' . $q . '%');
            })
            ->select('id', DB::raw('CONCAT(name, " (", email, ")") as text'), 'email')
            ->paginate(10, ['*'], 'page', $page);

        return response()->json($students);
    }

    public function dashboard()
    {
        $user = Auth::user();

        // Get the parent's wards (students)
        $wards = $user->students()->with(['schoolClass', 'hostel'])->get();

        // Get unread announcements
        $unreadAnnouncements = $user->unreadAnnouncementsCount();

        // Fetch current term and session
        $currentTerm = Term::where('is_current', 1)->first();
        $currentSession = Session::where('is_current', 1)->first();

        // You can calculate ward statistics if needed
        $wardTestsTaken = [];
        $wardPassedTests = [];
        $wardFailedTests = [];

        // Optional: Calculate test statistics for each ward
        foreach ($wards as $ward) {
            // Add your logic here to fetch test statistics
            $wardTestsTaken[$ward->id] = 0; // Replace with actual query
            $wardPassedTests[$ward->id] = 0; // Replace with actual query
            $wardFailedTests[$ward->id] = 0; // Replace with actual query
        }

        return view('parents.dashboard', compact(
            'wards',
            'unreadAnnouncements',
            'currentTerm',
            'currentSession',
            'wardTestsTaken',
            'wardPassedTests',
            'wardFailedTests'
        ));
    }

    /**
     * Fetch guardian details for a student.
     */
    public function getStudentGuardian($studentId)
    {
        $student = User::where('user_type', 4)->findOrFail($studentId);
        return response()->json([
            'guardian_name' => $student->guardian_name,
            'guardian_email' => $student->guardian_email,
            'guardian_phone' => $student->guardian_phone,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'parent_name' => 'required|string|max:255',
            'parent_email' => 'required|email|unique:users,email',
            'parent_phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6',
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:users,id',
        ]);

        $parent = User::create([
            'name' => $request->parent_name,
            'email' => $request->parent_email,
            'phone' => $request->parent_phone,
            'password' => Hash::make($request->password),
            'user_type' => 5,
            'added_by' => Auth::id(),
        ]);

        // Attach students (assuming belongsToMany relationship)
        $parent->students()->attach($request->student_ids);

        return redirect()->route('parent.add')->with('success', 'Parent activated successfully.');
    }

    public function edit(User $parent)
    {
        $parent->load('students');
        return view('edit_parent', compact('parent'));
    }

    public function update(Request $request, User $parent)
    {
        $request->validate([
            'parent_name' => 'required|string|max:255',
            'parent_email' => 'required|email|unique:users,email,' . $parent->id,
            'parent_phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6',
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:users,id',
        ]);

        $updateData = [
            'name' => $request->parent_name,
            'email' => $request->parent_email,
            'phone' => $request->parent_phone,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $parent->update($updateData);

        // Sync students
        $parent->students()->sync($request->student_ids);

        return redirect()->route('parents.index')->with('success', 'Parent updated successfully.');
    }

    public function destroy(User $parent)
    {
        $parent->students()->detach();
        $parent->delete();

        return redirect()->route('parents.index')->with('success', 'Parent deleted successfully.');
    }

    public function myWards(Request $request)
    {
        $user = Auth::user();
        $students = collect();

        try {
            Log::info('User accessing myWards: ID=' . $user->id . ', Type=' . $user->user_type);

            if ($user->user_type == 5) { // Parent
                if (!method_exists($user, 'students')) {
                    Log::error('Students method not found on User model for user ID: ' . $user->id);
                    return view('my_ward_index', compact('students', 'user'))->with('error', 'Relationship configuration error.');
                }
                $students = $user->students()->with('class')->get();
                Log::info('Fetched students for parent ID ' . $user->id . ': ' . $students->toJson());
                if ($students->isEmpty()) {
                    Log::warning('No students found for parent ID ' . $user->id);
                }
            } else { // Admin or Superadmin (assuming user_type 1 or 2)
                $query = User::where('user_type', 4)->with('class'); // Students only

                if ($request->filled('search_student')) {
                    $query->where('name', 'like', '%' . $request->search_student . '%')
                        ->orWhere('admission_no', 'like', '%' . $request->search_student . '%');
                }

                $students = $query->paginate(12); // Paginate for admin view
                Log::info('Fetched students for admin: ' . $students->toJson());
            }
        } catch (\Exception $e) {
            Log::error('Error in myWards: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            return view('my_ward_index', compact('students', 'user'))->with('error', 'An error occurred while fetching wards: ' . $e->getMessage());
        }

        return view('my_ward_index', compact('students', 'user'));
    }


    /**
     * View Fee Prospectus for a specific ward (student)
     */
    public function wardFeeProspectus($studentId)
    {
        $parent = Auth::user();

        // Ensure the student belongs to this parent and load class + section
        $student = $parent->students()
            ->where('users.id', $studentId)
            ->with(['schoolClass.section'])  // Eager load class and its section
            ->firstOrFail();

        // Safety check: student must have a class
        if (!$student->class_id || !$student->schoolClass || !$student->schoolClass->section) {
            return redirect()->back()->with('error', 'This student is not assigned to any class/section.');
        }

        $sectionId = $student->schoolClass->section_id;  // ← Reliable source

        // Get the current session for this SECTION
        $currentSession = Session::where('section_id', $sectionId)
            ->where('is_current', true)
            ->first();

        if (!$currentSession) {
            return redirect()->back()->with('error', 'No current academic session is set for ' . $student->schoolClass->section->section_name . '.');
        }

        // Get the current term for this session
        $currentTerm = Term::where('session_id', $currentSession->id)
            ->where('is_current', true)
            ->first();

        if (!$currentTerm) {
            return redirect()->back()->with('error', 'No current term is set for the active session.');
        }

        $currentTerm->load('session');

        // Fetch the fee prospectus using class_id and section_id
        $prospectus = FeeProspectus::where('section_id', $sectionId)
            ->where('class_id', $student->class_id)
            ->where('term_id', $currentTerm->id)
            ->first();

        if (!$prospectus) {
            return redirect()->back()->with(
                'info',
                'No fee prospectus has been set for ' .
                    $student->schoolClass->name .
                    ' in the current term (' . $currentTerm->name . ').'
            );
        }

        // **FIX: Add the missing $sections variable**
        // Fetch all sections (if needed for the view)
        $sections = Section::all();

        return view('parents.wards.ward_fee_prospectus', compact('student', 'prospectus', 'currentTerm', 'sections'));
    }

    public function transactionHistory(Request $request)
    {
        $parent = Auth::user();

        // Get parent's wards
        $wards = $parent->students()->with('schoolClass')->get();

        if ($wards->isEmpty()) {
            return view('parents.transaction_history', compact('wards'))
                ->with('info', 'No wards are linked to your account yet.');
        }

        // Build payment query
        $query = Payment::whereIn('student_id', $wards->pluck('id'))
            ->with(['student', 'section', 'schoolClass', 'term.session'])
            ->orderBy('created_at', 'desc');

        // Filters
        if ($request->filled('ward_id')) {
            $query->where('student_id', $request->ward_id);
        }

        if ($request->filled('session_id')) {
            $query->where('session_id', $request->session_id);
        }

        if ($request->filled('term_id')) {
            $query->where('term_id', $request->term_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $payments = $query->paginate(20);
        $payments->appends($request->query());

        // Fetch filter options
        $sessions = Session::whereIn('section_id', $wards->pluck('schoolClass.section_id')->unique())
            ->orderBy('name', 'desc')
            ->get();

        $terms = collect();
        if ($request->filled('session_id')) {
            $terms = Term::where('session_id', $request->session_id)->get();
        }

        return view('parents.transaction_history', compact(
            'wards',
            'payments',
            'sessions',
            'terms'
        ));
    }
}
