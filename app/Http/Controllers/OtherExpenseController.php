<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\OtherExpense;
use App\Models\Section;
use App\Models\Session;
use App\Models\Term;

class OtherExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = OtherExpense::with(['section', 'session', 'term', 'createdBy']);

        if ($request->filled('filter_section')) {
            $query->where('section_id', $request->filter_section);
        }

        if ($request->filled('filter_session')) {
            $query->where('session_id', $request->filter_session);
        }

        if ($request->filled('filter_term')) {
            $query->where('term_id', $request->filter_term);
        }

        if ($request->filled('filter_description')) {
            $query->where('description', 'like', '%' . $request->filter_description . '%');
        }

        if ($request->filled('filter_date_from')) {
            $query->whereDate('created_at', '>=', $request->filter_date_from);
        }

        if ($request->filled('filter_date_to')) {
            $query->whereDate('created_at', '<=', $request->filter_date_to);
        }

        $expenses = $query->latest()->paginate(10);
        $sections = Section::all();
        $sessions = Session::orderByDesc('name')->get();
        $terms    = Term::all();

        return view('other_expense_manage', compact('expenses', 'sections', 'sessions', 'terms'));
    }

    public function create()
    {
        $sections = Section::all();

        // Pre-load current session and term for the form
        $currentSession = Session::where('is_current', true)->first();
        $currentTerm    = $currentSession
            ? Term::where('session_id', $currentSession->id)->where('is_current', true)->first()
            : null;

        $sessions = Session::orderByDesc('name')->get();
        $terms    = $currentSession
            ? Term::where('session_id', $currentSession->id)->get()
            : collect();

        return view('other_expense_create', compact(
            'sections', 'sessions', 'terms', 'currentSession', 'currentTerm'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount'      => 'required|numeric|min:0',
            'description' => 'required|string',
            'section_id'  => 'nullable|exists:sections,id',
            'session_id'  => 'required|exists:school_sessions,id',
            'term_id'     => 'required|exists:terms,id',
        ]);

        $validated['created_by'] = Auth::id();

        OtherExpense::create($validated);

        return redirect()->route('other.expense.manage')
            ->with('success', 'Other Expense created successfully.');
    }

    public function edit(OtherExpense $otherExpense)
    {
        $sections = Section::all();
        $sessions = Session::orderByDesc('name')->get();
        $terms    = Term::where('session_id', $otherExpense->session_id)->get();

        return view('other_expense_edit', compact('otherExpense', 'sections', 'sessions', 'terms'));
    }

    public function update(Request $request, OtherExpense $otherExpense)
    {
        $validated = $request->validate([
            'amount'      => 'required|numeric|min:0',
            'description' => 'required|string',
            'section_id'  => 'nullable|exists:sections,id',
            'session_id'  => 'required|exists:school_sessions,id',
            'term_id'     => 'required|exists:terms,id',
        ]);

        $otherExpense->update($validated);

        return redirect()->route('other.expense.manage')
            ->with('success', 'Other Expense updated successfully.');
    }

    public function destroy(OtherExpense $otherExpense)
    {
        $otherExpense->delete();

        return redirect()->route('other.expense.manage')
            ->with('success', 'Other Expense deleted successfully.');
    }

    public function show(OtherExpense $otherExpense)
    {
        $otherExpense->load(['section', 'session', 'term', 'createdBy']);
        $sections = Section::all();

        return view('other_expense_show', compact('otherExpense', 'sections'));
    }

    /**
     * Fetch terms for a session via AJAX (used in create/edit forms).
     */
    public function getTerms($sessionId)
    {
        $terms = Term::where('session_id', $sessionId)
            ->select('id', 'name', 'is_current')
            ->get();

        return response()->json($terms);
    }
}