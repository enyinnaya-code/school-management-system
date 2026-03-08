<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;


class ResultSheetService
{
    public function loadTemplateStructure(int $templateId): array
    {
        $subjects = DB::table('result_sheet_subjects')
            ->where('template_id', $templateId)
            ->orderBy('sort_order')
            ->get();

        foreach ($subjects as $subject) {
            $subject->subcategories = DB::table('result_sheet_subcategories')
                ->where('subject_id', $subject->id)
                ->orderBy('sort_order')
                ->get();

            $subject->items = DB::table('result_sheet_items')
                ->where('subject_id', $subject->id)
                ->whereNull('subcategory_id')
                ->orderBy('sort_order')
                ->get();

            foreach ($subject->subcategories as $sub) {
                $sub->items = DB::table('result_sheet_items')
                    ->where('subject_id', $subject->id)
                    ->where('subcategory_id', $sub->id)
                    ->orderBy('sort_order')
                    ->get();
            }
        }

        return $subjects->toArray();
    }
}