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
        // Fetch sections (filtered by created_by if user is authenticated)
        $sections = Section::when(Auth::check(), function ($query) {
            return $query->where('created_by', Auth::id());
        })->get();

        // Get the selected section ID from the request (if any)
        $sectionId = $request->query('section_id');

        // Fetch sessions with their terms, optionally filtered by section_id
        $sessions = Session::when($sectionId, function ($query) use ($sectionId) {
            return $query->where('section_id', $sectionId);
        })
            ->when(Auth::check(), function ($query) {
                return $query->whereHas('section', function ($q) {
                    $q->where('created_by', Auth::id());
                });
            })
            ->with(['section', 'terms'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('manage_session', compact('sessions', 'sections', 'sectionId'));
    }

     public function create(Request $request)
    {
        // Debug: Log authentication status
        Log::info('Session Create - Auth Check', [
            'is_authenticated' => Auth::check(),
            'user_id' => Auth::id(),
            'user' => Auth::user()
        ]);

        // Fetch sections with better error handling
        $sections = Section::when(Auth::check(), function ($query) {
            return $query->where('created_by', Auth::id());
        })->get();

        // Debug: Log sections retrieved
        Log::info('Sections Retrieved', [
            'count' => $sections->count(),
            'sections' => $sections->pluck('id', 'section_name')->toArray()
        ]);

        // Get the selected section ID from the request (if any)
        $sectionId = $request->query('section_id');

        // Debug: Log selected section
        if ($sectionId) {
            Log::info('Selected Section', ['section_id' => $sectionId]);
        }

        // Fetch the current session and its terms for the selected section (if provided)
        $currentSession = $sectionId
            ? Session::where('section_id', $sectionId)->where('is_current', true)->with('terms')->first()
            : null;

        // Debug: Log current session
        if ($currentSession) {
            Log::info('Current Session Found', [
                'session_id' => $currentSession->id,
                'session_name' => $currentSession->name
            ]);
        }

        // If no sections found and user is authenticated, log a warning
        if ($sections->isEmpty() && Auth::check()) {
            Log::warning('No sections found for authenticated user', [
                'user_id' => Auth::id()
            ]);
        }

        return view('set_session', compact('sections', 'currentSession', 'sectionId'));
    }

    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:school_sessions,name,NULL,id,section_id,' . $request->section_id,
            ],
            'section_id' => 'required|exists:sections,id',
        ]);

        // Check if the section belongs to the authenticated user (if needed)
        if (Auth::check()) {
            $section = Section::where('id', $request->section_id)
                ->where('created_by', Auth::id())
                ->first();

            if (!$section) {
                Log::warning('Section not found or not owned by user', [
                    'section_id' => $request->section_id,
                    'user_id' => Auth::id(),
                ]);
                return redirect()->route('sessions.create', ['section_id' => $request->section_id])
                    ->with('error', 'Selected section not found or you do not have permission to set a session for it.');
            }
        }

        // Check if there is already a current session for the section
        $currentSession = Session::where('section_id', $request->section_id)
            ->where('is_current', true)
            ->first();

        if ($currentSession) {
            Log::warning('Attempt to create new session while current session exists', [
                'section_id' => $request->section_id,
                'current_session_id' => $currentSession->id,
            ]);
            return redirect()->route('sessions.create', ['section_id' => $request->section_id])
                ->with('error', 'A current session already exists for this section. Please unset the current session before adding a new one.');
        }

        // Start a database transaction
        DB::beginTransaction();
        try {
            // Create and set the new session as current
            $session = Session::create([
                'name' => $request->name,
                'section_id' => $request->section_id,
                'is_current' => true,
            ]);

            // Create three terms for the session
            Term::create([
                'session_id' => $session->id,
                'name' => 'First Term',
                'is_current' => true, // Set First Term as current by default
            ]);
            Term::create([
                'session_id' => $session->id,
                'name' => 'Second Term',
                'is_current' => false,
            ]);
            Term::create([
                'session_id' => $session->id,
                'name' => 'Third Term',
                'is_current' => false,
            ]);

            Log::info('New session created with terms', [
                'session_id' => $session->id,
                'name' => $session->name,
                'section_id' => $session->section_id,
            ]);

            DB::commit();

            return redirect()->route('sessions.create', ['section_id' => $request->section_id])
                ->with('success', 'Session and terms set successfully for the selected section.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to set session', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'section_id' => $request->section_id,
                'name' => $request->name,
            ]);

            return redirect()->route('sessions.create', ['section_id' => $request->section_id])
                ->with('error', 'Failed to set session: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Session $session)
    {
        // Validate the request
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:school_sessions,name,' . $session->id . ',id,section_id,' . $session->section_id,
            ],
        ]);

        // Check if the session belongs to the authenticated user's section
        if (Auth::check() && $session->section->created_by !== Auth::id()) {
            Log::warning('Unauthorized attempt to update session', [
                'session_id' => $session->id,
                'user_id' => Auth::id(),
            ]);
            return redirect()->route('sessions.index')
                ->with('error', 'You do not have permission to update this session.');
        }

        // Start a database transaction
        DB::beginTransaction();
        try {
            // Update the session name
            $session->update([
                'name' => $request->name,
            ]);

            Log::info('Session updated', [
                'session_id' => $session->id,
                'name' => $session->name,
                'section_id' => $session->section_id,
            ]);

            DB::commit();

            return redirect()->route('sessions.index')
                ->with('success', 'Session updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to update session', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'session_id' => $session->id,
            ]);

            return redirect()->route('sessions.index')
                ->with('error', 'Failed to update session: ' . $e->getMessage());
        }
    }

    public function set(Request $request, Session $session)
    {
        // Check if the session is already current
        if ($session->is_current) {
            Log::warning('Attempt to set an already current session', [
                'session_id' => $session->id,
                'user_id' => Auth::id(),
            ]);
            return redirect()->route('sessions.index')
                ->with('error', 'This session is already set as the current session.');
        }

        // Check if the session belongs to the authenticated user's section
        if (Auth::check() && $session->section->created_by !== Auth::id()) {
            Log::warning('Unauthorized attempt to set session', [
                'session_id' => $session->id,
                'user_id' => Auth::id(),
            ]);
            return redirect()->route('sessions.index')
                ->with('error', 'You do not have permission to set this session.');
        }

        // Start a database transaction
        DB::beginTransaction();
        try {
            // Unset any existing current session for the same section
            Session::where('section_id', $session->section_id)
                ->where('is_current', true)
                ->update(['is_current' => false]);

            // Set the selected session as current
            $session->update(['is_current' => true]);

            // Set the first term as current by default
            Term::where('session_id', $session->id)->update(['is_current' => false]);
            Term::where('session_id', $session->id)->where('name', 'First Term')->update(['is_current' => true]);

            Log::info('Session set as current', [
                'session_id' => $session->id,
                'section_id' => $session->section_id,
            ]);

            DB::commit();

            return redirect()->route('sessions.index')
                ->with('success', 'Session set as current successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to set session', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'session_id' => $session->id,
            ]);

            return redirect()->route('sessions.index')
                ->with('error', 'Failed to set session: ' . $e->getMessage());
        }
    }

    public function unset(Request $request, Session $session)
    {
        // Check if the session is current
        if (!$session->is_current) {
            Log::warning('Attempt to unset a non-current session', [
                'session_id' => $session->id,
                'user_id' => Auth::id(),
            ]);
            return redirect()->route('sessions.index')
                ->with('error', 'Only the current session can be unset.');
        }

        // Check if the session belongs to the authenticated user's section
        if (Auth::check() && $session->section->created_by !== Auth::id()) {
            Log::warning('Unauthorized attempt to unset session', [
                'session_id' => $session->id,
                'user_id' => Auth::id(),
            ]);
            return redirect()->route('sessions.index')
                ->with('error', 'You do not have permission to unset this session.');
        }

        // Start a database transaction
        DB::beginTransaction();
        try {
            // Set is_current to false for session and its terms
            $session->update(['is_current' => false]);
            Term::where('session_id', $session->id)->update(['is_current' => false]);

            Log::info('Session unset', [
                'session_id' => $session->id,
                'section_id' => $session->section_id,
            ]);

            DB::commit();

            return redirect()->route('sessions.index')
                ->with('success', 'Session unset successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to unset session', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'session_id' => $session->id,
            ]);

            return redirect()->route('sessions.index')
                ->with('error', 'Failed to unset session: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, Session $session)
    {
        // Check if the session belongs to the authenticated user's section
        if (Auth::check() && $session->section->created_by !== Auth::id()) {
            Log::warning('Unauthorized attempt to delete session', [
                'session_id' => $session->id,
                'user_id' => Auth::id(),
            ]);
            return redirect()->route('sessions.index')
                ->with('error', 'You do not have permission to delete this session.');
        }

        // Start a database transaction
        DB::beginTransaction();
        try {
            // Delete the session (terms are automatically deleted due to cascade)
            $session->delete();

            Log::info('Session deleted', [
                'session_id' => $session->id,
                'section_id' => $session->section_id,
            ]);

            DB::commit();

            return redirect()->route('sessions.index')
                ->with('success', 'Session and its terms deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to delete session', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'session_id' => $session->id,
            ]);

            return redirect()->route('sessions.index')
                ->with('error', 'Failed to delete session: ' . $e->getMessage());
        }
    }

    public function setTerm(Request $request, Term $term)
    {
        // Check if the term's session belongs to the authenticated user's section
        if (Auth::check() && $term->session->section->created_by !== Auth::id()) {
            Log::warning('Unauthorized attempt to set term', [
                'term_id' => $term->id,
                'user_id' => Auth::id(),
            ]);
            return redirect()->route('sessions.index')
                ->with('error', 'You do not have permission to set this term.');
        }

        // Start a database transaction
        DB::beginTransaction();
        try {
            // Unset any existing current term for the same session
            Term::where('session_id', $term->session_id)
                ->where('is_current', true)
                ->update(['is_current' => false]);

            // Set the selected term as current
            $term->update(['is_current' => true]);

            Log::info('Term set as current', [
                'term_id' => $term->id,
                'session_id' => $term->session_id,
            ]);

            DB::commit();

            return redirect()->route('sessions.index')
                ->with('success', 'Term set as current successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to set term', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'term_id' => $term->id,
            ]);

            return redirect()->route('sessions.index')
                ->with('error', 'Failed to set term: ' . $e->getMessage());
        }
    }

    public function getBySection(Request $request)
    {
        $sectionId = $request->query('section_id');
        $sessions = Session::where('section_id', $sectionId)->get(['id', 'name', 'is_current']);
        return response()->json($sessions);
    }

    public function getTermsBySession(Request $request)
    {
        $sessionId = $request->query('session_id');
        $terms = Term::where('session_id', $sessionId)->get(['id', 'name', 'is_current']);
        return response()->json($terms);
    }
}