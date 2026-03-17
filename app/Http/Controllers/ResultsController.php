<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Session;
use App\Models\Term;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\ResultAccessRestriction;
use App\Models\TermSetting;

class ResultAccessController extends Controller
{
    public function index(Request $request)
    {
        // ── Session / Term ────────────────────────────────────────────────────
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

        // ── Filters ───────────────────────────────────────────────────────────
        $search          = $request->input('search', '');
        $filterSectionId = $request->input('section_id', '');
        $filterClassId   = $request->input('class_id', '');
        $filterStatus    = $request->input('status', ''); // 'blocked', 'active', or ''
        $perPage         = (int) $request->input('per_page', 20);
        $perPage         = in_array($perPage, [10, 20, 50, 100]) ? $perPage : 20;

        // ── Sections dropdown ─────────────────────────────────────────────────
        $sections = Section::orderBy('section_name')->get(['id', 'section_name']);

        // ── Classes dropdown (filtered by section) ────────────────────────────
        $classesQuery = SchoolClass::orderBy('name');
        if ($filterSectionId) {
            $classesQuery->where('section_id', $filterSectionId);
        }
        $classes = $classesQuery->get(['id', 'name', 'section_id']);

        // ── Students ──────────────────────────────────────────────────────────
        $studentsQuery = User::where('user_type', 4)
            ->with(['class.section'])
            ->orderBy('name');

        if ($search) {
            $studentsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('admission_no', 'like', "%{$search}%");
            });
        }

        if ($filterClassId) {
            $studentsQuery->where('class_id', $filterClassId);
        } elseif ($filterSectionId) {
            $studentsQuery->whereHas('class', fn($q) => $q->where('section_id', $filterSectionId));
        }

        // Status filter — blocked/active requires knowing blocked IDs first
        if ($filterStatus === 'blocked' && $selectedSession && $selectedTerm) {
            $blockedStudentIds = ResultAccessRestriction::where('session_id', $selectedSession->id)
                ->where('term_id', $selectedTerm->id)
                ->pluck('student_id');
            $studentsQuery->whereIn('id', $blockedStudentIds);
        } elseif ($filterStatus === 'active' && $selectedSession && $selectedTerm) {
            $blockedStudentIds = ResultAccessRestriction::where('session_id', $selectedSession->id)
                ->where('term_id', $selectedTerm->id)
                ->pluck('student_id');
            $studentsQuery->whereNotIn('id', $blockedStudentIds);
        }

        $students = $studentsQuery->paginate($perPage)->withQueryString();

        // ── Blocked students ──────────────────────────────────────────────────
        $blockedIds     = collect();
        $blockedReasons = collect();

        if ($selectedSession && $selectedTerm) {
            $restrictions   = ResultAccessRestriction::where('session_id', $selectedSession->id)
                ->where('term_id', $selectedTerm->id)
                ->get(['student_id', 'reason']);
            $blockedIds     = $restrictions->pluck('student_id');
            $blockedReasons = $restrictions->pluck('reason', 'student_id');
        }

        // ── Term settings ─────────────────────────────────────────────────────
        $termSettings = ($selectedSession && $selectedTerm)
            ? TermSetting::where('session_id', $selectedSession->id)
                ->where('term_id', $selectedTerm->id)->first()
            : null;

        return view('results.settings.index', compact(
            'sessions', 'selectedSession', 'terms', 'selectedTerm',
            'students', 'blockedIds', 'blockedReasons', 'termSettings',
            'sections', 'classes',
            'search', 'filterSectionId', 'filterClassId', 'filterStatus', 'perPage'
        ));
    }


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
                    'reason'     => $request->reason ?: 'Owing school fees',
                    'blocked_by' => Auth::id(),
                ]
            );
            return back()->with('success', 'Student blocked from viewing results.');
        }

        ResultAccessRestriction::where('student_id', $request->student_id)
            ->where('session_id', $request->session_id)
            ->where('term_id', $request->term_id)
            ->delete();

        return back()->with('success', 'Student access restored.');
    }


    public function bulkBlock(Request $request)
    {
        $request->validate([
            'student_ids'   => 'required|array|min:1',
            'student_ids.*' => 'exists:users,id',
            'session_id'    => 'required|exists:school_sessions,id',
            'term_id'       => 'required|exists:terms,id',
            'reason'        => 'nullable|string|max:500',
        ]);

        $reason = $request->reason ?: 'Owing school fees';
        $now    = now();

        foreach ($request->student_ids as $sid) {
            ResultAccessRestriction::updateOrCreate(
                [
                    'student_id' => $sid,
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


    public function bulkUnblock(Request $request)
    {
        $request->validate([
            'student_ids'   => 'required|array|min:1',
            'student_ids.*' => 'exists:users,id',
            'session_id'    => 'required|exists:school_sessions,id',
            'term_id'       => 'required|exists:terms,id',
        ]);

        $deleted = ResultAccessRestriction::whereIn('student_id', $request->student_ids)
            ->where('session_id', $request->session_id)
            ->where('term_id', $request->term_id)
            ->delete();

        return back()->with('success', count($request->student_ids) . ' student(s) unblocked successfully.');
    }


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
                'resumption_date' => $request->resumption_date ?: null,
                'school_fees'     => $request->school_fees     ?: null,
                'fees_payable_by' => $request->fees_payable_by ?: null,
                'notes'           => $request->notes           ?: null,
                'updated_by'      => Auth::id(),
                'created_by'      => Auth::id(),
            ]
        );

        return back()->with('success', 'Term settings saved successfully.');
    }
}