<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OtherExpense;
use App\Models\Section;

class OtherExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = OtherExpense::with('section');

        // Apply filters
        if ($request->filled('filter_section')) {
            $query->where('section_id', $request->filter_section);
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

        $expenses = $query->paginate(10);

        $sections = Section::all();

        return view('other_expense_manage', compact('expenses', 'sections'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $sections = Section::all();
        return view('other_expense_create', compact('sections'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string',
            'section_id' => 'nullable|exists:sections,id',
        ]);

        OtherExpense::create($validated);

        return redirect()->route('other.expense.manage')->with('success', 'Other Expense created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OtherExpense $otherExpense)
    {
        $sections = Section::all();
        return view('other_expense_edit', compact('otherExpense', 'sections'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OtherExpense $otherExpense)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string',
            'section_id' => 'nullable|exists:sections,id',
        ]);

        $otherExpense->update($validated);

        return redirect()->route('other.expense.manage')->with('success', 'Other Expense updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OtherExpense $otherExpense)
    {
        $otherExpense->delete();
        return redirect()->route('other.expense.manage')->with('success', 'Other Expense deleted successfully.');
    }

    public function show(OtherExpense $otherExpense)
    {
        $sections = Section::all();
        return view('other_expense_show', compact('otherExpense', 'sections'));
    }
}