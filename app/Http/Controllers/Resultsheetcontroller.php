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
            ->leftJoin('users as u', 't.created_by', '=', 'u.id')
            ->selectRaw('t.*, s.section_name, u.name as creator_name')
            ->orderByDesc('t.created_at')
            ->paginate(15);

        foreach ($templates as $template) {
            $template->subject_count      = DB::table('result_sheet_subjects')
                ->where('template_id', $template->id)->count();
            $template->applicable_classes = json_decode($template->applicable_classes ?? '[]');
            $template->rating_columns     = json_decode($template->rating_columns ?? '[]');
            $template->footer_fields      = json_decode($template->footer_fields ?? '{}', true);
            $template->class_names        = SchoolClass::whereIn('id', $template->applicable_classes)
                ->pluck('name')->implode(', ');

            if (empty($template->term_name) && $template->term_id) {
                $template->term_name = Term::find($template->term_id)?->name;
            }
        }

        return view('result_sheets.index', compact('templates'));
    }

    // =====================================================================
    // HELPER — fetch distinct term names from the terms table
    // Used by create() and edit()
    // =====================================================================

   private function fetchTermsForDropdown(): array
{
    return ['First Term', 'Second Term', 'Third Term'];
}

    // =====================================================================
    // CREATE
    // =====================================================================

    public function create()
    {
        $sections = Section::all();
        $terms    = $this->fetchTermsForDropdown();   // ← collection of {id, name}

        return view('result_sheets.create', compact('sections', 'terms'));
    }

    // =====================================================================
    // STORE
    // =====================================================================

    public function store(Request $request)
    {
        $this->resolveTermName($request);

        $request->validate([
            'name'                 => 'required|string|max:255',
            'description'          => 'nullable|string',
            'section_id'           => 'nullable|exists:sections,id',
            'term_name'            => 'required|string|max:100',
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
                'term_id'            => null,
                'term_name'          => $request->term_name,
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

        // Backfill term_name from term_id for legacy rows
        if (empty($template->term_name) && $template->term_id) {
            $template->term_name = Term::find($template->term_id)?->name;
        }

        $sections         = Section::all();
        $terms            = $this->fetchTermsForDropdown();   // ← collection of {id, name}
        $existingSubjects = $this->loadTemplateStructureForEdit($id);

        return view('result_sheets.edit', compact(
            'template',
            'sections',
            'existingSubjects',
            'terms'
        ));
    }

    // =====================================================================
    // UPDATE
    // =====================================================================

    public function update(Request $request, $id)
    {
        $this->resolveTermName($request);

        Log::info('ResultSheet UPDATE', [
            'id'        => $id,
            'term_name' => $request->input('term_name'),
            'term_id'   => $request->input('term_id'),
        ]);

        $request->validate([
            'name'                 => 'required|string|max:255',
            'description'          => 'nullable|string',
            'section_id'           => 'nullable|exists:sections,id',
            'term_name'            => 'required|string|max:100',
            'applicable_classes'   => 'required|array|min:1',
            'applicable_classes.*' => 'exists:school_classes,id',
            'rating_columns'       => 'required|array|min:2',
            'rating_columns.*'     => 'required|string|max:50',
            'subjects_json'        => 'required|string',
        ]);

        $subjects = json_decode($request->subjects_json, true);

        if (empty($subjects)) {
            return back()
                ->withErrors(['subjects_json' => 'No subject data found. Raw: ' . substr($request->subjects_json, 0, 200)])
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
                DB::table('result_sheet_templates')->where('id', $id)->update([
                    'name'               => $request->name,
                    'description'        => $request->description,
                    'section_id'         => $request->section_id,
                    'term_id'            => null,
                    'term_name'          => $request->term_name,
                    'applicable_classes' => json_encode($request->applicable_classes),
                    'rating_columns'     => json_encode(array_values($request->rating_columns)),
                    'footer_fields'      => json_encode($footerFields),
                    'updated_by'         => Auth::id(),
                    'updated_at'         => now(),
                ]);

                $subjectIds = DB::table('result_sheet_subjects')
                    ->where('template_id', $id)->pluck('id');

                DB::table('result_sheet_items')->whereIn('subject_id', $subjectIds)->delete();
                DB::table('result_sheet_subcategories')->whereIn('subject_id', $subjectIds)->delete();
                DB::table('result_sheet_subjects')->where('template_id', $id)->delete();

                $this->saveSubjectsJson($id, $subjects);
            });
        } catch (\Throwable $e) {
            Log::error('ResultSheet UPDATE failed', ['error' => $e->getMessage()]);
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
    // VIEW
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

        if (empty($template->term_name) && $template->term_id) {
            $template->term_name = Term::find($template->term_id)?->name;
        }

        $term    = $template->term_id ? Term::find($template->term_id) : null;
        $session = Session::where('is_current', true)->first();

        $applicableClasses = count($template->applicable_classes)
            ? SchoolClass::whereIn('id', $template->applicable_classes)->orderBy('name')->get()
            : collect();

        $service  = new ResultSheetService();
        $subjects = $service->loadTemplateStructure($templateId);

        $students = count($template->applicable_classes)
            ? User::with('schoolClass')->where('user_type', 4)
            ->whereIn('class_id', $template->applicable_classes)->orderBy('name')->get()
            : collect();

        $ratingCounts    = [];
        $matchingTermIds = Term::where('name', $template->term_name)->pluck('id');

        if ($students->count() && $session && $matchingTermIds->count()) {
            $counts = DB::table('result_sheet_ratings')
                ->where('template_id', $templateId)
                ->where('session_id', $session->id)
                ->whereIn('term_id', $matchingTermIds)
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

        if (empty($template->term_name) && $template->term_id) {
            $template->term_name = Term::find($template->term_id)?->name;
        }

        $sessions          = Session::orderByDesc('name')->get();
        $selectedSessionId = $request->input('session_id')
            ?? Session::where('is_current', true)->value('id');
        $selectedSession   = Session::find($selectedSessionId);

        $terms = $selectedSession
            ? Term::where('session_id', $selectedSession->id)
            ->where('name', $template->term_name)
            ->orderBy('name')->get()
            : collect();

        $selectedTermId = $request->input('term_id') ?? $terms->first()?->id;
        $selectedTerm   = Term::find($selectedTermId);

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
                ->get()->keyBy('item_id');

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
                        'item_id' => $itemId,
                        'student_id' => $studentId,
                        'session_id' => $sessionId,
                        'term_id' => $termId
                    ],
                    [
                        'template_id' => $templateId,
                        'rating_value' => $value ?: null,
                        'rated_by' => Auth::id(),
                        'updated_at' => now(),
                        'created_at' => now()
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

        if (empty($template->term_name) && $template->term_id) {
            $template->term_name = Term::find($template->term_id)?->name;
        }

        $student = User::findOrFail($studentId);
        $class   = SchoolClass::find($student->class_id);
        $section = Section::find($class?->section_id);

        $sessions        = Session::orderByDesc('name')->get();
        $selectedSession = Session::find($request->input('session_id'))
            ?? Session::where('is_current', true)->first();

        $terms = $selectedSession
            ? Term::where('session_id', $selectedSession->id)
            ->where('name', $template->term_name)
            ->orderBy('name')->get()
            : collect();

        $selectedTerm = Term::find($request->input('term_id')) ?? $terms->first();
        $service      = new ResultSheetService();
        $subjects     = $service->loadTemplateStructure($templateId);

        $allItemIds = collect($subjects)->flatMap(function ($subject) {
            $ids = collect($subject->items)->pluck('id');
            foreach ($subject->subcategories as $sub) {
                $ids = $ids->merge(collect($sub->items)->pluck('id'));
            }
            return $ids;
        });

        $termIds    = $terms->pluck('id');
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
        $rows = DB::table('terms')
            ->select('id', 'name')
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->orderByRaw("FIELD(name, 'First Term', 'Second Term', 'Third Term'), name ASC")
            ->get()
            ->unique('name')
            ->values();

        $terms = $rows->map(fn($t) => [
            'id'         => $t->id,
            'name'       => $t->name,
            'is_current' => DB::table('terms')
                ->where('name', $t->name)->where('is_current', true)->exists(),
        ])->values();

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
     * Ensure $request->term_name is populated before validation.
     * Handles both new forms (name="term_name") and legacy cached forms (name="term_id").
     */
    private function resolveTermName(Request $request): void
    {
        if (!empty($request->input('term_name'))) {
            return; // already set — nothing to do
        }

        $termId = $request->input('term_id');
        if ($termId && is_numeric($termId)) {
            $name = DB::table('terms')->where('id', $termId)->value('name');
            if ($name) {
                $request->merge(['term_name' => $name]);
            }
        }
    }

    private function saveSubjectsJson(int $templateId, array $subjects): void
    {
        foreach ($subjects as $sortOrder => $subjectData) {
            $subjectId = DB::table('result_sheet_subjects')->insertGetId([
                'template_id'    => $templateId,
                'course_id'      => (!empty($subjectData['course_id']) && $subjectData['course_id'] != 0)
                    ? $subjectData['course_id'] : null,
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
