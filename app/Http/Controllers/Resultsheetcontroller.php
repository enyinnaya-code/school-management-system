<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Section;
use App\Models\SchoolClass;
use App\Models\User;
use App\Models\Session;
use App\Models\Term;
use App\Models\Course;
use Illuminate\Support\Facades\Log;
use App\Services\ResultSheetService;



class ResultSheetController extends Controller
{
    // =====================================================================
    // INDEX
    // =====================================================================

    public function index()
    {
        $templates = DB::table('result_sheet_templates as t')
            ->leftJoin('sections as s', 't.section_id', '=', 's.id')
            ->leftJoin('terms as tr', 't.term_id', '=', 'tr.id')
            ->leftJoin('school_sessions as se', 'tr.session_id', '=', 'se.id')
            ->leftJoin('users as u', 't.created_by', '=', 'u.id')
            ->selectRaw('t.*, s.section_name, u.name as creator_name, tr.name as term_name, se.name as session_name')
            ->orderByDesc('t.created_at')
            ->paginate(15);

        foreach ($templates as $template) {
            $template->subject_count      = DB::table('result_sheet_subjects')
                ->where('template_id', $template->id)->count();
            $template->applicable_classes = json_decode($template->applicable_classes ?? '[]');
            $template->rating_columns     = json_decode($template->rating_columns ?? '[]');
            $template->footer_fields      = json_decode($template->footer_fields ?? '{}', true);

            // Get class names
            $template->class_names = SchoolClass::whereIn('id', $template->applicable_classes)
                ->pluck('name')->implode(', ');
        }

        return view('result_sheets.index', compact('templates'));
    }

    // =====================================================================
    // CREATE
    // =====================================================================

    public function create()
    {
        $sections = Section::all();
        return view('result_sheets.create', compact('sections'));
    }

    // =====================================================================
    // STORE
    // =====================================================================

    public function store(Request $request)
    {
        $request->validate([
            'name'                 => 'required|string|max:255',
            'description'          => 'nullable|string',
            'section_id'           => 'nullable|exists:sections,id',
            'term_id'              => 'required|exists:terms,id',
            'applicable_classes'   => 'required|array|min:1',
            'applicable_classes.*' => 'exists:school_classes,id',
            'rating_columns'       => 'required|array|min:2',
            'rating_columns.*'     => 'required|string|max:50',
            'subjects_json'        => 'required|string',
        ]);

        $subjects = json_decode($request->subjects_json, true);

        if (empty($subjects)) {
            return back()
                ->withErrors(['subjects_json' => 'Please add at least one subject with sub-topics and items.'])
                ->withInput();
        }

        $footerFields = [
            'footer_remark'        => (bool) $request->footer_remark,
            'footer_class_teacher' => (bool) $request->footer_class_teacher,
            'footer_headmistress'  => (bool) $request->footer_headmistress,
            'footer_reopening'     => (bool) $request->footer_reopening,
        ];

        DB::transaction(function () use ($request, $subjects, $footerFields) {
            $templateId = DB::table('result_sheet_templates')->insertGetId([
                'name'               => $request->name,
                'description'        => $request->description,
                'section_id'         => $request->section_id,
                'term_id'            => $request->term_id,
                'applicable_classes' => json_encode($request->applicable_classes),
                'rating_columns'     => json_encode(array_values($request->rating_columns)),
                'footer_fields'      => json_encode($footerFields),
                'is_active'          => 1,
                'created_by'         => Auth::id(),
                'updated_by'         => Auth::id(),
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);

            $this->saveSubjectsJson($templateId, $subjects);
        });

        return redirect()->route('result_sheets.index')
            ->with('success', 'Result sheet template created successfully.');
    }

    // =====================================================================
    // EDIT
    // =====================================================================

    public function edit($id)
    {
        $template = DB::table('result_sheet_templates')->find($id);
        abort_if(!$template, 404);

        $template->applicable_classes = json_decode($template->applicable_classes ?? '[]');
        $template->rating_columns     = json_decode($template->rating_columns ?? '[]');
        $template->footer_fields      = json_decode($template->footer_fields ?? '{}', true);

        $sections = Section::all();

        // Load existing subjects structure for pre-filling the JS store
        $existingSubjects = $this->loadTemplateStructureForEdit($id);

        return view('result_sheets.edit', compact('template', 'sections', 'existingSubjects'));
    }

    // =====================================================================
    // UPDATE
    // =====================================================================

    public function update(Request $request, $id)
    {
        // ── DEBUG: log exactly what arrived ──────────────────────────────
        Log::info('ResultSheet UPDATE called', [
            'id'                  => $id,
            'name'                => $request->input('name'),
            'term_id'             => $request->input('term_id'),
            'section_id'          => $request->input('section_id'),
            'applicable_classes'  => $request->input('applicable_classes'),
            'rating_columns'      => $request->input('rating_columns'),
            'subjects_json_len'   => strlen($request->input('subjects_json', '')),
            'subjects_json_raw'   => substr($request->input('subjects_json', ''), 0, 300),
        ]);
        // ─────────────────────────────────────────────────────────────────

        $request->validate([
            'name'                 => 'required|string|max:255',
            'description'          => 'nullable|string',
            'section_id'           => 'nullable|exists:sections,id',
            'term_id'              => 'required|exists:terms,id',
            'applicable_classes'   => 'required|array|min:1',
            'applicable_classes.*' => 'exists:school_classes,id',
            'rating_columns'       => 'required|array|min:2',
            'rating_columns.*'     => 'required|string|max:50',
            'subjects_json'        => 'required|string',
        ]);

        $subjects = json_decode($request->subjects_json, true);

        Log::info('ResultSheet UPDATE decoded subjects', [
            'count'    => count($subjects ?? []),
            'subjects' => $subjects,
        ]);

        if (empty($subjects)) {
            return back()
                ->withErrors(['subjects_json' => 'subjects_json decoded to empty. Raw value: ' . substr($request->subjects_json, 0, 200)])
                ->withInput();
        }

        $footerFields = [
            'footer_remark'        => (bool) $request->footer_remark,
            'footer_class_teacher' => (bool) $request->footer_class_teacher,
            'footer_headmistress'  => (bool) $request->footer_headmistress,
            'footer_reopening'     => (bool) $request->footer_reopening,
        ];

        try {
            DB::transaction(function () use ($request, $id, $subjects, $footerFields) {
                $updated = DB::table('result_sheet_templates')->where('id', $id)->update([
                    'name'               => $request->name,
                    'description'        => $request->description,
                    'section_id'         => $request->section_id,
                    'term_id'            => $request->term_id,
                    'applicable_classes' => json_encode($request->applicable_classes),
                    'rating_columns'     => json_encode(array_values($request->rating_columns)),
                    'footer_fields'      => json_encode($footerFields),
                    'updated_by'         => Auth::id(),
                    'updated_at'         => now(),
                ]);

                Log::info('ResultSheet template row update result', [
                    'id'      => $id,
                    'rows'    => $updated,
                ]);

                // Wipe old subjects/subcategories/items and re-insert
                $subjectIds = DB::table('result_sheet_subjects')
                    ->where('template_id', $id)->pluck('id');

                Log::info('ResultSheet wiping subjects', ['subject_ids' => $subjectIds]);

                DB::table('result_sheet_items')
                    ->whereIn('subject_id', $subjectIds)->delete();
                DB::table('result_sheet_subcategories')
                    ->whereIn('subject_id', $subjectIds)->delete();
                DB::table('result_sheet_subjects')
                    ->where('template_id', $id)->delete();

                $this->saveSubjectsJson($id, $subjects);

                Log::info('ResultSheet saveSubjectsJson done');
            });
        } catch (\Throwable $e) {
            Log::error('ResultSheet UPDATE transaction failed', [
                'error'   => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ]);
            return back()
                ->withErrors(['update_error' => 'Update failed: ' . $e->getMessage()])
                ->withInput();
        }

        return redirect()->route('result_sheets.index')
            ->with('success', 'Template updated successfully.');
    }

    // =====================================================================
    // DESTROY
    // =====================================================================

    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            $subjectIds = DB::table('result_sheet_subjects')
                ->where('template_id', $id)->pluck('id');

            DB::table('result_sheet_ratings')->where('template_id', $id)->delete();
            DB::table('result_sheet_items')->whereIn('subject_id', $subjectIds)->delete();
            DB::table('result_sheet_subcategories')->whereIn('subject_id', $subjectIds)->delete();
            DB::table('result_sheet_subjects')->where('template_id', $id)->delete();
            DB::table('result_sheet_templates')->where('id', $id)->delete();
        });

        return redirect()->route('result_sheets.index')
            ->with('success', 'Template and all associated data deleted.');
    }

    // =====================================================================
    // TOGGLE ACTIVE
    // =====================================================================

    public function toggleActive($id)
    {
        $template = DB::table('result_sheet_templates')->find($id);
        abort_if(!$template, 404);

        DB::table('result_sheet_templates')->where('id', $id)->update([
            'is_active'  => !$template->is_active,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Status updated.');
    }

    // =====================================================================
    // VIEW — template detail page (from manage/index)
    // Shows structure + all students in applicable classes with rating counts
    // =====================================================================

    public function viewSheet($templateId)
    {
        $template = DB::table('result_sheet_templates as t')
            ->leftJoin('sections as s', 't.section_id', '=', 's.id')
            ->leftJoin('users as u', 't.created_by', '=', 'u.id')
            ->selectRaw('t.*, s.section_name, u.name as creator_name')
            ->where('t.id', $templateId)
            ->first();
        abort_if(!$template, 404);

        $template->rating_columns     = json_decode($template->rating_columns ?? '[]');
        $template->footer_fields      = json_decode($template->footer_fields ?? '{}', true);
        $template->applicable_classes = json_decode($template->applicable_classes ?? '[]');

        // Term & session
        $term    = Term::find($template->term_id);
        $session = $term ? Session::find($term->session_id) : null;

        // Applicable classes
        $applicableClasses = count($template->applicable_classes)
            ? SchoolClass::whereIn('id', $template->applicable_classes)->orderBy('name')->get()
            : collect();

        // Load structure
        $service  = new ResultSheetService();
        $subjects = $service->loadTemplateStructure($templateId);

        // All students in those classes
        $students = count($template->applicable_classes)
            ? User::with('schoolClass')
            ->where('user_type', 4)
            ->whereIn('class_id', $template->applicable_classes)
            ->orderBy('name')
            ->get()
            : collect();

        // Rating counts per student for this template+term+session
        $ratingCounts = [];
        if ($students->count() && $term && $session) {
            $counts = DB::table('result_sheet_ratings')
                ->where('template_id', $templateId)
                ->where('session_id', $session->id)
                ->where('term_id', $term->id)
                ->whereNotNull('rating_value')
                ->whereIn('student_id', $students->pluck('id'))
                ->select('student_id', DB::raw('COUNT(*) as cnt'))
                ->groupBy('student_id')
                ->get();
            foreach ($counts as $row) {
                $ratingCounts[$row->student_id] = $row->cnt;
            }
        }

        return view('result_sheets.view', compact(
            'template',
            'term',
            'session',
            'applicableClasses',
            'subjects',
            'students',
            'ratingCounts'
        ));
    }

    // =====================================================================
    // RATE STUDENTS
    // =====================================================================

    public function rateStudents(Request $request, $templateId)
    {
        $template = DB::table('result_sheet_templates')->find($templateId);
        abort_if(!$template, 404);

        $template->rating_columns     = json_decode($template->rating_columns ?? '[]');
        $template->applicable_classes = json_decode($template->applicable_classes ?? '[]');

        $sessions = Session::orderByDesc('name')->get();

        $selectedSessionId = $request->input('session_id');
        if (!$selectedSessionId) {
            $currentSession    = Session::where('is_current', true)->first();
            $selectedSessionId = $currentSession?->id;
        }
        $selectedSession = Session::find($selectedSessionId);
        $terms           = $selectedSession
            ? $selectedSession->terms()->orderBy('name')->get()
            : collect();

        $selectedTermId = $request->input('term_id');
        if (!$selectedTermId && $selectedSession) {
            $currentTerm    = $terms->where('is_current', true)->first();
            $selectedTermId = $currentTerm?->id ?? $terms->first()?->id;
        }
        $selectedTerm = Term::find($selectedTermId);

        $applicableClassIds = $template->applicable_classes;
        $classes = count($applicableClassIds)
            ? SchoolClass::whereIn('id', $applicableClassIds)->orderBy('name')->get()
            : SchoolClass::orderBy('name')->get();

        $selectedClassId = $request->input('class_id');
        $selectedClass   = $selectedClassId ? SchoolClass::find($selectedClassId) : null;

        $students = $selectedClass
            ? User::where('user_type', 4)->where('class_id', $selectedClass->id)->orderBy('name')->get()
            : collect();

        $selectedStudentId = $request->input('student_id');
        $selectedStudent   = $selectedStudentId ? User::find($selectedStudentId) : null;

        $service  = new ResultSheetService();
        $subjects = $service->loadTemplateStructure($templateId);

        $existingRatings = [];
        if ($selectedStudent && $selectedSession && $selectedTerm) {
            $allItemIds = collect($subjects)->flatMap(function ($subject) {
                $ids = collect($subject->items)->pluck('id');
                foreach ($subject->subcategories as $sub) {
                    $ids = $ids->merge(collect($sub->items)->pluck('id'));
                }
                return $ids;
            });

            $ratings = DB::table('result_sheet_ratings')
                ->where('student_id', $selectedStudent->id)
                ->where('session_id', $selectedSession->id)
                ->where('term_id', $selectedTerm->id)
                ->whereIn('item_id', $allItemIds)
                ->get()
                ->keyBy('item_id');

            foreach ($ratings as $itemId => $rating) {
                $existingRatings[$itemId] = $rating->rating_value;
            }
        }

        return view('result_sheets.rate', compact(
            'template',
            'subjects',
            'sessions',
            'selectedSession',
            'terms',
            'selectedTerm',
            'classes',
            'selectedClass',
            'students',
            'selectedStudent',
            'existingRatings'
        ));
    }

    // =====================================================================
    // SAVE RATINGS
    // =====================================================================

    public function saveRatings(Request $request, $templateId)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'session_id' => 'required|exists:school_sessions,id',
            'term_id'    => 'required|exists:terms,id',
            'ratings'    => 'nullable|array',
        ]);

        $ratings   = $request->input('ratings', []);
        $studentId = $request->student_id;
        $sessionId = $request->session_id;
        $termId    = $request->term_id;

        DB::transaction(function () use ($ratings, $studentId, $sessionId, $termId, $templateId) {
            foreach ($ratings as $itemId => $value) {
                DB::table('result_sheet_ratings')->updateOrInsert(
                    [
                        'item_id'    => $itemId,
                        'student_id' => $studentId,
                        'session_id' => $sessionId,
                        'term_id'    => $termId,
                    ],
                    [
                        'template_id'  => $templateId,
                        'rating_value' => $value ?: null,
                        'rated_by'     => Auth::id(),
                        'updated_at'   => now(),
                        'created_at'   => now(),
                    ]
                );
            }
        });

        return back()->with('success', 'Ratings saved successfully.');
    }

    // =====================================================================
    // PRINT SHEET
    // =====================================================================

    public function printSheet(Request $request, $templateId, $studentId)
    {
        $template = DB::table('result_sheet_templates')->find($templateId);
        abort_if(!$template, 404);
        $template->rating_columns = json_decode($template->rating_columns ?? '[]');
        $template->footer_fields  = json_decode($template->footer_fields ?? '{}', true);

        $student = User::findOrFail($studentId);
        $class   = SchoolClass::find($student->class_id);
        $section = Section::find($class?->section_id);

        $sessions        = Session::orderByDesc('name')->get();
        $selectedSession = Session::find($request->input('session_id'))
            ?? Session::where('is_current', true)->first();
        $terms           = $selectedSession
            ? $selectedSession->terms()->orderBy('name')->get()
            : collect();
        $selectedTerm    = Term::find($request->input('term_id'))
            ?? $terms->where('is_current', true)->first()
            ?? $terms->first();

        $service  = new ResultSheetService();
        $subjects = $service->loadTemplateStructure($templateId); // ← CORRECT
        $termIds    = $terms->pluck('id');

        $allItemIds = collect($subjects)->flatMap(function ($subject) {
            $ids = collect($subject->items)->pluck('id');
            foreach ($subject->subcategories as $sub) {
                $ids = $ids->merge(collect($sub->items)->pluck('id'));
            }
            return $ids;
        });

        $ratingsRaw = DB::table('result_sheet_ratings')
            ->where('student_id', $studentId)
            ->where('session_id', $selectedSession?->id)
            ->whereIn('term_id', $termIds)
            ->whereIn('item_id', $allItemIds)
            ->get();

        $ratings = [];
        foreach ($ratingsRaw as $r) {
            $ratings[$r->term_id][$r->item_id] = $r->rating_value;
        }

        return view('result_sheets.print', compact(
            'template',
            'student',
            'class',
            'section',
            'subjects',
            'sessions',
            'selectedSession',
            'terms',
            'selectedTerm',
            'ratings'
        ));
    }

    // =====================================================================
    // API HELPERS
    // =====================================================================

    public function getTermsBySection(Request $request)
    {
        $terms = Term::with('session')
            ->get()
            ->map(fn($t) => [
                'id'         => $t->id,
                'name'       => ($t->session->name ?? '') . ' — ' . $t->name,
                'is_current' => (bool) $t->is_current,
            ])
            ->sortByDesc('is_current')
            ->values();

        return response()->json(['terms' => $terms]);
    }

    public function getSubjectsByClasses(Request $request)
    {
        $classIds = array_filter(explode(',', $request->query('class_ids', '')));

        if (empty($classIds)) {
            return response()->json([]);
        }

        $courses = Course::whereHas('schoolClasses', function ($q) use ($classIds) {
            $q->whereIn('school_classes.id', $classIds);
        })
            ->orderBy('course_name')
            ->get(['id', 'course_name'])
            ->unique('id')
            ->values();

        return response()->json($courses);
    }

    // =====================================================================
    // PRIVATE HELPERS
    // =====================================================================

    /**
     * Insert subjects, subcategories, items from the decoded subjects_json array.
     */
    private function saveSubjectsJson(int $templateId, array $subjects): void
    {
        foreach ($subjects as $sortOrder => $subjectData) {
            $subjectId = DB::table('result_sheet_subjects')->insertGetId([
                'template_id'    => $templateId,
                'course_id'      => (!empty($subjectData['course_id']) && $subjectData['course_id'] != 0) ? $subjectData['course_id'] : null,
                'subject_number' => $subjectData['subject_number'],
                'subject_name'   => $subjectData['course_name'],
                'sort_order'     => $sortOrder,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            foreach (($subjectData['subtopics'] ?? []) as $subOrder => $st) {
                $subId = DB::table('result_sheet_subcategories')->insertGetId([
                    'subject_id' => $subjectId,
                    'label'      => $st['label'] ?? null,
                    'name'       => $st['name'],
                    'sort_order' => $subOrder,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach (($st['items'] ?? []) as $iOrder => $itemText) {
                    DB::table('result_sheet_items')->insert([
                        'subject_id'     => $subjectId,
                        'subcategory_id' => $subId,
                        'item_text'      => $itemText,
                        'sort_order'     => $iOrder,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Load structure for display/rating.
     */
  

    /**
     * Load structure shaped for the JS store (edit form pre-fill).
     * Returns array compatible with store.subjects in the blade JS.
     */
    private function loadTemplateStructureForEdit(int $templateId): array
    {
        $subjects = DB::table('result_sheet_subjects')
            ->where('template_id', $templateId)
            ->orderBy('sort_order')
            ->get();

        $result = [];
        foreach ($subjects as $subject) {
            $subcategories = DB::table('result_sheet_subcategories')
                ->where('subject_id', $subject->id)
                ->orderBy('sort_order')
                ->get();

            $subtopics = [];
            foreach ($subcategories as $sub) {
                $items = DB::table('result_sheet_items')
                    ->where('subject_id', $subject->id)
                    ->where('subcategory_id', $sub->id)
                    ->orderBy('sort_order')
                    ->pluck('item_text')
                    ->toArray();

                $subtopics[] = [
                    'label' => $sub->label ?? '',
                    'name'  => $sub->name,
                    'items' => $items,
                ];
            }

            $result[] = [
                'course_id'      => $subject->course_id ?: ('existing_' . $subject->id),
                'course_name'    => $subject->subject_name,
                'subject_number' => $subject->subject_number,
                'subtopics'      => $subtopics,
            ];
        }

        return $result;
    }
}
