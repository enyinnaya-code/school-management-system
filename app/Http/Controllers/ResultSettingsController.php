<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Section;
use App\Models\SchoolClass;
use App\Models\PrimaryResultSection;
use App\Models\PrimaryResultClass;
use App\Models\ClassSubjectLimit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ResultSettingsController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // PRIMARY RESULT CLASS SETTINGS
    // ─────────────────────────────────────────────────────────────

    public function primaryResultClass()
    {
        $sections = Section::orderBy('section_name')->get();

        // Currently saved primary section
        $primarySection = PrimaryResultSection::with('section')->first();

        // Currently saved primary classes
        $primaryClassIds = PrimaryResultClass::pluck('school_class_id')->toArray();

        // Classes in the saved primary section (for display on page load)
        $classes = collect();
        if ($primarySection) {
            $classes = SchoolClass::where('section_id', $primarySection->section_id)
                ->orderBy('name')
                ->get();
        }

        return view('results.settings.primary_result_class', compact(
            'sections',
            'primarySection',
            'primaryClassIds',
            'classes'
        ));
    }

    public function getClassesBySection($sectionId)
    {
        $classes = SchoolClass::where('section_id', $sectionId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($classes);
    }

    public function savePrimaryResultClass(Request $request)
    {
        $request->validate([
            'section_id'  => 'required|exists:sections,id',
            'class_ids'   => 'required|array|min:1',
            'class_ids.*' => 'exists:school_classes,id',
        ]);

        // Update or create the primary section record
        $existing = PrimaryResultSection::first();
        if ($existing) {
            $existing->update([
                'section_id' => $request->section_id,
                'updated_by' => Auth::id(),
            ]);
        } else {
            PrimaryResultSection::create([
                'section_id' => $request->section_id,
                'created_by' => Auth::id(),
            ]);
        }

        // DELETE instead of TRUNCATE — safe inside or outside transactions
        PrimaryResultClass::query()->delete();

        foreach ($request->class_ids as $classId) {
            PrimaryResultClass::create([
                'school_class_id' => $classId,
            ]);
        }

        return redirect()->back()->with('success', 'Primary result classes saved successfully.');
    }

    // ─────────────────────────────────────────────────────────────
    // CUSTOM SUBJECT NUMBER SETTINGS
    // ─────────────────────────────────────────────────────────────

    public function customSubjectNo()
    {
        $sections = Section::with(['classes' => function ($q) {
            $q->orderBy('name');
        }])->orderBy('section_name')->get();

        // Get existing limits keyed by class ID for easy lookup
        $existingLimits = ClassSubjectLimit::all()->keyBy('school_class_id');

        return view('results.settings.custom_subject_no', compact(
            'sections',
            'existingLimits'
        ));
    }

    public function saveCustomSubjectNo(Request $request)
    {
        $request->validate([
            'limits'              => 'nullable|array',
            'limits.*.class_id'   => 'required|exists:school_classes,id',
            'limits.*.min'        => 'required|integer|min:1|max:20',
            'limits.*.max'        => 'required|integer|min:1|max:20|gte:limits.*.min',
        ]);

        DB::transaction(function () use ($request) {
            // First remove limits for classes that were unchecked
            $submittedClassIds = collect($request->input('limits', []))
                ->pluck('class_id')
                ->filter()
                ->toArray();

            // Delete limits for classes not in the current submission
            ClassSubjectLimit::whereNotIn('school_class_id', $submittedClassIds)->delete();

            // Upsert limits for submitted classes
            foreach ($request->input('limits', []) as $limit) {
                if (empty($limit['class_id'])) continue;

                $existing = ClassSubjectLimit::where('school_class_id', $limit['class_id'])->first();

                if ($existing) {
                    $existing->update([
                        'min_subjects' => $limit['min'],
                        'max_subjects' => $limit['max'],
                        'updated_by'   => Auth::id(),
                    ]);
                } else {
                    ClassSubjectLimit::create([
                        'school_class_id' => $limit['class_id'],
                        'min_subjects'    => $limit['min'],
                        'max_subjects'    => $limit['max'],
                        'created_by'      => Auth::id(),
                    ]);
                }
            }
        });

        return redirect()->back()->with('success', 'Custom subject number settings saved successfully.');
    }
}
