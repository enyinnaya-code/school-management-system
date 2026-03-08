<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Session;
use App\Models\Section;
use App\Models\Term;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SessionController extends Controller
{
    public function index(Request $request)
    {
        $sessions = Session::with(['terms'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('manage_session', compact('sessions'));
    }

    public function create(Request $request)
    {
        $currentSession = Session::where('is_current', true)->with('terms')->first();

        return view('set_session', compact('currentSession'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:school_sessions,name',
            ],
        ]);

        // Block creation if a current session already exists
        $currentSession = Session::where('is_current', true)->first();

        if ($currentSession) {
            return redirect()->route('sessions.create')
                ->with('error', 'A current session already exists (' . $currentSession->name . '). Please unset it before creating a new one.');
        }

        DB::beginTransaction();
        try {
            $session = Session::create([
                'name'       => $request->name,
                'section_id' => null, // no longer section-scoped
                'is_current' => true,
            ]);

            Term::create(['session_id' => $session->id, 'name' => 'First Term',  'is_current' => true]);
            Term::create(['session_id' => $session->id, 'name' => 'Second Term', 'is_current' => false]);
            Term::create(['session_id' => $session->id, 'name' => 'Third Term',  'is_current' => false]);

            DB::commit();

            return redirect()->route('sessions.create')
                ->with('success', 'Session "' . $session->name . '" created and set as current for the whole school.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create session', ['error' => $e->getMessage()]);
            return redirect()->route('sessions.create')
                ->with('error', 'Failed to create session: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Session $session)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:school_sessions,name,' . $session->id,
            ],
        ]);

        DB::beginTransaction();
        try {
            $session->update(['name' => $request->name]);
            DB::commit();

            return redirect()->route('sessions.index')
                ->with('success', 'Session updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('sessions.index')
                ->with('error', 'Failed to update session: ' . $e->getMessage());
        }
    }

    public function set(Request $request, Session $session)
    {
        if ($session->is_current) {
            return redirect()->route('sessions.index')
                ->with('error', 'This session is already the current session.');
        }

        DB::beginTransaction();
        try {
            // Unset ALL current sessions school-wide
            Session::where('is_current', true)->update(['is_current' => false]);

            // Set this session as current
            $session->update(['is_current' => true]);

            // Reset terms: only First Term active
            Term::where('session_id', $session->id)->update(['is_current' => false]);
            Term::where('session_id', $session->id)
                ->where('name', 'First Term')
                ->update(['is_current' => true]);

            DB::commit();

            return redirect()->route('sessions.index')
                ->with('success', '"' . $session->name . '" is now the current session for the whole school.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('sessions.index')
                ->with('error', 'Failed to set session: ' . $e->getMessage());
        }
    }

    public function unset(Request $request, Session $session)
    {
        if (!$session->is_current) {
            return redirect()->route('sessions.index')
                ->with('error', 'Only the current session can be unset.');
        }

        DB::beginTransaction();
        try {
            $session->update(['is_current' => false]);
            Term::where('session_id', $session->id)->update(['is_current' => false]);

            DB::commit();

            return redirect()->route('sessions.index')
                ->with('success', 'Session unset. No session is currently active.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('sessions.index')
                ->with('error', 'Failed to unset session: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, Session $session)
    {
        if ($session->is_current) {
            return redirect()->route('sessions.index')
                ->with('error', 'Cannot delete the current session. Please unset it first.');
        }

        DB::beginTransaction();
        try {
            $session->delete();
            DB::commit();

            return redirect()->route('sessions.index')
                ->with('success', 'Session and its terms deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('sessions.index')
                ->with('error', 'Failed to delete session: ' . $e->getMessage());
        }
    }

    public function setTerm(Request $request, Term $term)
    {
        DB::beginTransaction();
        try {
            // Unset all terms in the same session
            Term::where('session_id', $term->session_id)
                ->update(['is_current' => false]);

            // Set selected term as current
            $term->update(['is_current' => true]);

            DB::commit();

            return redirect()->route('sessions.index')
                ->with('success', '"' . $term->name . '" is now the current term.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('sessions.index')
                ->with('error', 'Failed to set term: ' . $e->getMessage());
        }
    }

    // ── API helpers ──────────────────────────────────────────

    public function getBySection(Request $request)
    {
        // section_id ignored — all sessions are school-wide now
        $sessions = Session::orderByDesc('name')->get(['id', 'name', 'is_current']);
        return response()->json($sessions);
    }

    public function getTermsBySession(Request $request)
    {
        $sessionId = $request->query('session_id');
        $terms = Term::where('session_id', $sessionId)
            ->orderBy('name')
            ->get(['id', 'name', 'is_current']);
        return response()->json($terms);
    }
}