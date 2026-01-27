<?php

namespace App\Http\Controllers;

use App\Models\MiscFee;
use App\Models\Section;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MiscFeeController extends Controller
{
    /**
     * Display a listing of the resource (Manage Misc Fee Types).
     */
    public function index(Request $request)
    {
        // Build the query
        $query = MiscFee::with(['section', 'createdBy'])
            ->orderBy('created_at', 'desc');

        // Apply filters if present
        if ($request->filled('filter_section')) {
            $query->where('section_id', $request->filter_section);
        }

        if ($request->filled('filter_name')) {
            $query->where('name', 'like', '%' . $request->filter_name . '%');
        }

        if ($request->filled('filter_amount_min')) {
            $query->where('amount', '>=', $request->filter_amount_min);
        }

        if ($request->filled('filter_amount_max')) {
            $query->where('amount', '<=', $request->filter_amount_max);
        }

        // Paginate the results
        $feeTypes = $query->paginate(10);

        // Get filter options
        $sections = Section::orderBy('section_name')->get();

        return view('misc_fee_manage', compact('feeTypes', 'sections'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $sections = Section::orderBy('section_name')->get();

        return view('misc_fee_create', compact('sections'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:misc_fee_types,name',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'section_id' => 'nullable|exists:sections,id',
        ]);

        DB::beginTransaction();
        try {
            MiscFee::create([
                'name' => $request->name,
                'description' => $request->description,
                'amount' => $request->amount,
                'section_id' => $request->section_id,
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            return redirect()
                ->route('misc.fee.manage')
                ->with('success', 'Miscellaneous fee type created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'Error creating fee type: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MiscFee $miscFee)
    {
        $sections = Section::orderBy('section_name')->get();

        return view('misc_fee_edit', compact('miscFee', 'sections'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MiscFee $miscFee)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:misc_fee_types,name,' . $miscFee->id,
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'section_id' => 'nullable|exists:sections,id',
        ]);

        $miscFee->update([
            'name' => $request->name,
            'description' => $request->description,
            'amount' => $request->amount,
            'section_id' => $request->section_id,
        ]);

        return redirect()
            ->route('misc.fee.manage')
            ->with('success', 'Miscellaneous fee type updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MiscFee $miscFee)
    {
        $miscFee->delete();

        return redirect()
            ->route('misc.fee.manage')
            ->with('success', 'Miscellaneous fee type deleted successfully!');
    }
}