public function studentResultUpload($studentId)
{
    $student = User::where('user_type', 4)->findOrFail($studentId);
    $class   = SchoolClass::findOrFail($student->class_id);
    $section = Section::find($class->section_id);
    $user    = Auth::user();

    // Security check for non-admin users
    if (!in_array($user->user_type, [1, 2])) {
        if (!$this->isTeacherAssignedToClass($user->id, $class->id)) {
            abort(403, 'You are not assigned to this class.');
        }
    }

    $currentSession = Session::where('is_current', true)->first();
    $currentTerm    = $currentSession?->terms()->where('is_current', true)->first();

    if (!$currentSession || !$currentTerm) {
        return redirect()->back()->with('error', 'No current academic session or term is set.');
    }

    // ── Check if this class has an active custom result sheet for the current term ──
    $sheetTemplate = DB::table('result_sheet_templates')
        ->where('term_id', $currentTerm->id)
        ->where('is_active', 1)
        ->get()
        ->first(function ($t) use ($class) {
            $classes = json_decode($t->applicable_classes ?? '[]', true);
            return in_array($class->id, $classes) || in_array((string) $class->id, $classes);
        });

    // Inside studentResultUpload
    if ($sheetTemplate) {
        $sheetTemplate->rating_columns = json_decode($sheetTemplate->rating_columns ?? '[]');
        $sheetTemplate->footer_fields  = json_decode($sheetTemplate->footer_fields ?? '{}', true);

        $service  = new ResultSheetService();
        $subjects = $service->loadTemplateStructure($sheetTemplate->id);

        $allItemIds = collect($subjects)->flatMap(function ($subject) {
            $ids = collect($subject->items)->pluck('id');
            foreach ($subject->subcategories as $sub) {
                $ids = $ids->merge(collect($sub->items)->pluck('id'));
            }
            return $ids;
        });

        $existingRatings = DB::table('result_sheet_ratings')
            ->where('student_id', $studentId)
            ->where('session_id', $currentSession->id)
            ->where('term_id', $currentTerm->id)
            ->whereIn('item_id', $allItemIds)
            ->pluck('rating_value', 'item_id');

        $footerData = DB::table('result_sheet_footer_data')
            ->where('student_id', $student->id)
            ->where('session_id', $currentSession->id)
            ->where('term_id', $currentTerm->id)
            ->where('template_id', $sheetTemplate->id)
            ->first();

        return view('student_result_sheet', compact(
            'student',
            'class',
            'section',
            'sheetTemplate',
            'subjects',
            'existingRatings',
            'currentSession',
            'currentTerm',
            'footerData'
        ));
    }

    // ── Fallback: standard numeric result upload ──
    $subjectsQuery = Course::whereHas('schoolClasses', function ($q) use ($class) {
        $q->where('school_classes.id', $class->id);
    })->orderBy('course_name');

    if (!in_array($user->user_type, [1, 2])) {
        $subjectsQuery->whereExists(function ($query) use ($user) {
            $query->select(DB::raw(1))
                ->from('course_user')
                ->whereColumn('course_user.course_id', 'courses.id')
                ->where('course_user.user_id', $user->id);
        });
    }

    $subjects        = $subjectsQuery->get(['id', 'course_name']);
    $existingResults = Result::where('student_id', $studentId)
        ->where('session_id', $currentSession->id)
        ->where('term_id', $currentTerm->id)
        ->get()
        ->keyBy('course_id');

    $sheetTemplate = null; // ← FIX: pass null so the blade doesn't throw undefined variable

    return view('student_result_upload', compact(
        'student',
        'class',
        'section',
        'subjects',
        'existingResults',
        'currentSession',
        'currentTerm',
        'sheetTemplate'  // ← FIX: include in compact
    ));
}