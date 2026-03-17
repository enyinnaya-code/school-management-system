<?php
// ── app/Http/Controllers/ResultAccessController.php ────────────────────

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Session;
use App\Models\Term;
use App\Models\SchoolClass;
use App\Models\ResultAccessRestriction;
use App\Models\TermSetting;

class ResultAccessController extends Controller
{
    // ═══════════════════════════════════════════════════════════════════
    // MAIN PAGE — shows both the restriction manager and term settings
    // ═══════════════════════════════════════════════════════════════════
    public function index(Request $request)
    {
        // Session / Term selection
        $sessions = Session::orderByDesc('name')->get();

        $selectedSessionId = $request->input('session_id');
        if (!$selectedSessionId) {
            $currentSession    = Session::where('is_current', true)->first();
            $selectedSessionId = $currentSession?->id;
        }
        $selectedSession = Session::find($selectedSessionId);

        $terms = $selectedSession
            ? $selectedSession->terms()->orderBy('name')->get()
            : collect();

        $selectedTermId = $request->input('term_id');
        if (!$selectedTermId && $selectedSession) {
            $currentTerm    = $terms->where('is_current', true)->first();
            $selectedTermId = $currentTerm?->id ?? $terms->first()?->id;
        }
        $selectedTerm = Term::find($selectedTermId);

        // All students (paginated + searchable)
        $search = $request->input('search');

        $studentsQuery = User::where('user_type', 4)
            ->with('class')
            ->orderBy('name');

        if ($search) {
            $studentsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('admission_no', 'like', "%{$search}%");
            });
        }

        // Filter by class if requested
        $filterClassId = $request->input('class_id');
        if ($filterClassId) {
            $studentsQuery->where('class_id', $filterClassId);
        }

        $students = $studentsQuery->paginate(20)->withQueryString();

        // Already blocked students for the selected session/term
        $blockedIds = collect();
        if ($selectedSession && $selectedTerm) {
            $blockedIds = ResultAccessRestriction::where('session_id', $selectedSession->id)
                ->where('term_id', $selectedTerm->id)
                ->pluck('student_id');
        }

        // Term settings for selected term
        $termSettings = null;
        if ($selectedSession && $selectedTerm) {
            $termSettings = TermSetting::where('session_id', $selectedSession->id)
                ->where('term_id', $selectedTerm->id)
                ->first();
        }

        // Classes for the filter dropdown
        $classes = SchoolClass::orderBy('name')->get(['id', 'name']);

        return view('results.settings.index', compact(
            'sessions',
            'selectedSession',
            'terms',
            'selectedTerm',
            'students',
            'blockedIds',
            'termSettings',
            'classes',
            'search',
            'filterClassId'
        ));
    }


    // ═══════════════════════════════════════════════════════════════════
    // BLOCK / UNBLOCK a student
    // ═══════════════════════════════════════════════════════════════════
    public function toggleBlock(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'session_id' => 'required|exists:school_sessions,id',
            'term_id'    => 'required|exists:terms,id',
            'action'     => 'required|in:block,unblock',
            'reason'     => 'nullable|string|max:500',
        ]);

        if ($request->action === 'block') {
            ResultAccessRestriction::updateOrCreate(
                [
                    'student_id' => $request->student_id,
                    'session_id' => $request->session_id,
                    'term_id'    => $request->term_id,
                ],
                [
                    'reason'     => $request->reason ?? 'Owing school fees',
                    'blocked_by' => Auth::id(),
                ]
            );
            return back()->with('success', 'Student has been blocked from viewing results.');
        }

        ResultAccessRestriction::where('student_id', $request->student_id)
            ->where('session_id', $request->session_id)
            ->where('term_id', $request->term_id)
            ->delete();

        return back()->with('success', 'Student access has been restored.');
    }


    // ═══════════════════════════════════════════════════════════════════
    // BULK BLOCK — block multiple students at once
    // ═══════════════════════════════════════════════════════════════════
    public function bulkBlock(Request $request)
    {
        $request->validate([
            'student_ids'   => 'required|array|min:1',
            'student_ids.*' => 'exists:users,id',
            'session_id'    => 'required|exists:school_sessions,id',
            'term_id'       => 'required|exists:terms,id',
            'reason'        => 'nullable|string|max:500',
        ]);

        $reason = $request->reason ?? 'Owing school fees';
        $now    = now();

        foreach ($request->student_ids as $studentId) {
            ResultAccessRestriction::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'session_id' => $request->session_id,
                    'term_id'    => $request->term_id,
                ],
                [
                    'reason'     => $reason,
                    'blocked_by' => Auth::id(),
                    'updated_at' => $now,
                ]
            );
        }

        return back()->with('success', count($request->student_ids) . ' student(s) blocked successfully.');
    }


    // ═══════════════════════════════════════════════════════════════════
    // SAVE TERM SETTINGS (resumption date, fees, payable-by date)
    // ═══════════════════════════════════════════════════════════════════
    public function saveTermSettings(Request $request)
    {
        $request->validate([
            'session_id'      => 'required|exists:school_sessions,id',
            'term_id'         => 'required|exists:terms,id',
            'resumption_date' => 'nullable|date',
            'school_fees'     => 'nullable|numeric|min:0',
            'fees_payable_by' => 'nullable|date',
            'notes'           => 'nullable|string|max:1000',
        ]);

        TermSetting::updateOrCreate(
            [
                'session_id' => $request->session_id,
                'term_id'    => $request->term_id,
            ],
            [
                'resumption_date' => $request->resumption_date  ?: null,
                'school_fees'     => $request->school_fees      ?: null,
                'fees_payable_by' => $request->fees_payable_by  ?: null,
                'notes'           => $request->notes            ?: null,
                'updated_by'      => Auth::id(),
                'created_by'      => Auth::id(),
            ]
        );

        return back()->with('success', 'Term settings saved successfully.');
    }
}