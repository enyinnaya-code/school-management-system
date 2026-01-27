<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\SalaryPayment;
use App\Models\User;
use App\Models\Section;
use App\Models\Term;
use App\Models\Session;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class ProcessPayrollController extends Controller
{
    /**
     * Display the form for processing salary.
     */
    public function processForm()
    {
        $sections = Section::with(['sessions' => function ($query) {
            $query->with(['terms']);
        }])->get();

        $currentMonth = Carbon::now('Africa/Lagos')->month;

        return view('process', compact('sections', 'currentMonth'));
    }

    /**
     * Fetch sessions for a section via AJAX.
     */
    public function getSessions(Request $request, $section_id)
    {
        $sessions = Session::where('section_id', $section_id)
            ->select('id', 'name', 'is_current')
            ->get();

        return response()->json($sessions);
    }

    /**
     * Fetch terms for a session via AJAX.
     */
    public function getTerms(Request $request, $session_id)
    {
        $terms = Term::where('session_id', $session_id)
            ->select('id', 'name', 'is_current')
            ->get();

        return response()->json($terms);
    }

    /**
     * Show preview of payrolls for processing.
     */
    public function processPreview(Request $request)
    {
        $request->validate([
            'section_id' => 'required|integer', // Now allows 0 or real ID
            'session_id' => 'required|exists:school_sessions,id',
            'term_id' => 'required|exists:terms,id',
            'month' => 'required|integer|between:1,12',
        ]);

        $section = Section::find($request->section_id);
        $session = Session::findOrFail($request->session_id);
        $term = Term::findOrFail($request->term_id);
        $month = $request->month;
        $currentYear = Carbon::now('Africa/Lagos')->year;

        // Validate relationships (skip if section_id is 0)
        if ($request->section_id != 0) {
            if ($term->session_id !== $session->id) {
                return back()->withErrors(['term_id' => 'The selected term does not belong to the chosen session.']);
            }
            if ($session->section_id !== $section->id) {
                return back()->withErrors(['session_id' => 'The selected session does not belong to the chosen section.']);
            }
        }

        // Get payrolls: either specific section OR administrative (section_id = 0)
        $payrolls = Payroll::where(function ($query) use ($request) {
            if ($request->section_id == 0) {
                $query->where('section_id', 0);
            } else {
                $query->where('section_id', $request->section_id);
            }
        })
            ->with(['employee', 'section'])
            ->get()
            ->sortBy(function ($payroll) {
                return $payroll->employee->name;
            });

        // Check if already processed
        $isProcessed = SalaryPayment::where('term_id', $request->term_id)
            ->where('month', $month)
            ->where('year', $currentYear)
            ->where(function ($query) use ($request) {
                if ($request->section_id == 0) {
                    $query->where('section_id', 0);
                } else {
                    $query->where('section_id', $request->section_id);
                }
            })
            ->exists();

        $monthName = date('F', mktime(0, 0, 0, $month, 10));

        $payrollData = [];
        foreach ($payrolls as $payroll) {
            $sectionName = $payroll->section_id == 0
                ? 'Administrative / Not Applicable'
                : ($payroll->section->section_name ?? 'N/A');

            if ($isProcessed) {
                $salaryPayment = SalaryPayment::where('payroll_id', $payroll->id)
                    ->where('month', $month)
                    ->where('year', $currentYear)
                    ->where('term_id', $request->term_id)
                    ->first();

                if ($salaryPayment) {
                    $payrollData[] = [
                        'payroll_id' => $payroll->id,
                        'employee_name' => $payroll->employee->name ?? 'N/A',
                        'basic_salary' => $salaryPayment->basic_salary,
                        'allowances' => $salaryPayment->allowances,
                        'deductions' => $salaryPayment->deductions,
                        'description' => $salaryPayment->description,
                        'bank_name' => $salaryPayment->bank_name,
                        'account_number' => $salaryPayment->account_number,
                        'section_name' => $sectionName,
                    ];
                }
            } else {
                $payrollData[] = [
                    'payroll_id' => $payroll->id,
                    'employee_name' => $payroll->employee->name ?? 'N/A',
                    'basic_salary' => $payroll->basic_salary,
                    'allowances' => $payroll->allowances,
                    'deductions' => $payroll->deductions ?? 0,
                    'description' => "{$monthName} Salary",
                    'bank_name' => $payroll->bank_name,
                    'account_number' => $payroll->account_number,
                    'section_name' => $sectionName,
                ];
            }
        }

        // For display: if section_id == 0, use a placeholder section name
        $displaySection = $request->section_id == 0
            ? (object)['section_name' => 'Administrative Staff']
            : $section;

        return view('process_preview', compact('payrollData', 'displaySection', 'session', 'term', 'month', 'isProcessed'));
    }

    /**
     * Confirm and process salary payments via form submission.
     */
    public function confirmProcess(Request $request)
    {
        $request->validate([
            'payrolls' => 'required|array|min:1',
            'payrolls.*.id' => 'required|exists:payrolls,id',
            'payrolls.*.basic_salary' => 'required|numeric|min:0',
            'payrolls.*.allowances' => 'required|numeric|min:0',
            'payrolls.*.deductions' => 'required|numeric|min:0',
            'payrolls.*.description' => 'required|string|max:255',
            'payrolls.*.bank_name' => 'required|string|max:255',
            'payrolls.*.account_number' => 'required|string|max:255',
            'section_id' => 'required|exists:sections,id',
            'term_id' => 'required|exists:terms,id',
            'month' => 'required|integer|between:1,12',
        ]);

        DB::beginTransaction();
        try {
            $term = Term::findOrFail($request->term_id);
            $session = Session::findOrFail($term->session_id);
            $currentYear = Carbon::now('Africa/Lagos')->year;

            $processedCount = 0;

            foreach ($request->payrolls as $payrollData) {
                $payroll = Payroll::findOrFail($payrollData['id']);

                $basicSalary = (float) $payrollData['basic_salary'];
                $allowances = (float) $payrollData['allowances'];
                $deductions = (float) $payrollData['deductions'];
                $total = $basicSalary + $allowances;
                $netPay = $total - $deductions;

                // Check if payment already exists for this month
                $existingPayment = SalaryPayment::where('payroll_id', $payroll->id)
                    ->where('month', $request->month)
                    ->where('year', $currentYear)
                    ->where('term_id', $request->term_id)
                    ->first();

                if ($existingPayment) {
                    // Update existing payment
                    $existingPayment->update([
                        'basic_salary' => $basicSalary,
                        'allowances' => $allowances,
                        'deductions' => $deductions,
                        'total' => $total,
                        'net_pay' => $netPay,
                        'bank_name' => $payrollData['bank_name'],
                        'account_number' => $payrollData['account_number'],
                        'description' => $payrollData['description'],
                        'processed_by' => Auth::id(),
                        'processed_at' => now(),
                    ]);
                } else {
                    // Create new payment record
                    SalaryPayment::create([
                        'payroll_id' => $payroll->id,
                        'employee_id' => $payroll->employee_id,
                        'section_id' => $request->section_id,
                        'session_id' => $session->id,
                        'term_id' => $request->term_id,
                        'month' => $request->month,
                        'year' => $currentYear,
                        'basic_salary' => $basicSalary,
                        'allowances' => $allowances,
                        'deductions' => $deductions,
                        'total' => $total,
                        'net_pay' => $netPay,
                        'bank_name' => $payrollData['bank_name'],
                        'account_number' => $payrollData['account_number'],
                        'description' => $payrollData['description'],
                        'status' => 'processed',
                        'processed_by' => Auth::id(),
                        'processed_at' => now(),
                    ]);
                }

                $processedCount++;
            }

            DB::commit();

            $message = $processedCount > 0
                ? "{$processedCount} salary payment(s) processed successfully!"
                : "No salary payments were processed.";

            return redirect()
                ->route('finance.payroll.process')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'Error processing salary payments: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display a listing of the payrolls.
     */
    public function index(Request $request)
    {
        // Build the query
        $query = Payroll::with(['employee', 'section'])->orderBy('created_at', 'desc');

        // Apply filters if present
        if ($request->filled('filter_name')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->filter_name . '%');
            });
        }

        if ($request->filled('filter_section')) {
            $query->whereHas('section', function ($q) use ($request) {
                $q->where('section_name', 'like', '%' . $request->filter_section . '%');
            });
        }

        if ($request->filled('filter_bank_name')) {
            $query->where('bank_name', 'like', '%' . $request->filter_bank_name . '%');
        }

        if ($request->filled('filter_salary_min')) {
            $query->where('basic_salary', '>=', $request->filter_salary_min);
        }

        if ($request->filled('filter_salary_max')) {
            $query->where('basic_salary', '<=', $request->filter_salary_max);
        }

        if ($request->filled('filter_date_from')) {
            $query->whereDate('created_at', '>=', $request->filter_date_from);
        }

        if ($request->filled('filter_date_to')) {
            $query->whereDate('created_at', '<=', $request->filter_date_to);
        }

        // Paginate the results
        $payrolls = $query->paginate(10);

        return view('payroll_capture_index', compact('payrolls'));
    }

    public function create()
    {
        // Only fetch existing users who are NOT already in payroll
        $employees = User::whereIn('user_type', [2, 3, 6, 7, 8, 9, 10])
            ->whereNotIn('id', Payroll::whereNotNull('employee_id')->pluck('employee_id'))
            ->orderBy('name')
            ->get();

        $sections = Section::orderBy('section_name')->get();

        return view('create_payroll', compact('employees', 'sections'));
    }


    public function getAllSessions()
    {
        $sessions = Session::select('id', 'name', 'is_current')
            ->orderByDesc('is_current')
            ->orderBy('name')
            ->get();

        return response()->json($sessions);
    }


    public function store(Request $request)
    {
        $request->validate([
            'payrolls' => 'required|array|min:1',
            'payrolls.*.basic_salary' => 'required|numeric|min:0',
            'payrolls.*.allowances' => 'nullable|numeric|min:0',
            'payrolls.*.section_id' => 'required|integer',
            'payrolls.*.bank_name' => 'required|string|max:255',
            'payrolls.*.account_number' => 'required|string|max:255',
        ]);

        // Get payrolls as a regular array (not overloaded property)
        $payrolls = $request->input('payrolls');

        // Custom validation
        foreach ($payrolls as $index => $data) {
            $hasEmployee = !empty($data['employee_id']);
            $hasName = !empty($data['staff_name'] ?? null);

            if (!$hasEmployee && !$hasName) {
                return back()
                    ->withErrors(["payrolls.$index" => "You must select an existing staff or enter a staff name."])
                    ->withInput();
            }

            if ($hasEmployee && $hasName) {
                // Optional: disallow both - remove staff_name if both provided
                unset($payrolls[$index]['staff_name']);
            }

            // Validate section_id
            $sectionId = $data['section_id'];
            if ($sectionId != 0 && !\App\Models\Section::where('id', $sectionId)->exists()) {
                return redirect()->back()
                    ->withErrors(["payrolls.$index.section_id" => "Invalid section selected."])
                    ->withInput();
            }
        }

        DB::beginTransaction();
        try {
            foreach ($payrolls as $payrollData) {
                // Prepare data for insertion - only include fields that exist in the table
                $insertData = [
                    'employee_id' => !empty($payrollData['employee_id']) ? $payrollData['employee_id'] : null,
                    'staff_name' => !empty($payrollData['staff_name']) ? $payrollData['staff_name'] : null,
                    'basic_salary' => $payrollData['basic_salary'],
                    'allowances' => $payrollData['allowances'] ?? 0,
                    'section_id' => $payrollData['section_id'],
                    'bank_name' => $payrollData['bank_name'],
                    'account_number' => $payrollData['account_number'],
                    'created_by' => Auth::id(),
                ];

                // Create payroll record
                Payroll::create($insertData);
            }

            DB::commit();

            $count = count($payrolls);
            return redirect()
                ->route('finance.payroll.create')
                ->with('success', "Successfully added {$count} staff member(s) to payroll!");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'Error processing payroll: ' . $e->getMessage())
                ->withInput();
        }
    }


    public function edit(Payroll $payroll)
    {
        // Fetch all users that could be employees (excluding certain types)
        $employees = User::whereNotIn('user_type', [1, 5, 4])
            ->orderBy('name')
            ->get();

        $sections = Section::orderBy('section_name')->get();

        return view('payroll_capture_edit', compact('payroll', 'employees', 'sections'));
    }


    public function update(Request $request, Payroll $payroll)
    {
        // Base validation rules
        $rules = [
            'basic_salary' => 'required|numeric|min:0',
            'allowances' => 'nullable|numeric|min:0',
            'section_id' => 'required|integer',
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
        ];

        // Determine if it's existing staff or contract staff
        if ($request->filled('employee_id')) {
            $rules['employee_id'] = 'required|exists:users,id';
            $rules['staff_name'] = 'nullable'; // Clear staff_name if employee_id is provided
        } elseif ($request->filled('staff_name')) {
            $rules['staff_name'] = 'required|string|max:255';
            $rules['employee_id'] = 'nullable';
        } else {
            return redirect()->back()
                ->withErrors(['employee_id' => 'You must select an existing staff or enter a staff name.'])
                ->withInput();
        }

        $request->validate($rules);

        // Custom check: section_id must be 0 or a valid existing section
        $sectionId = $request->section_id;
        if ($sectionId != 0 && !\App\Models\Section::where('id', $sectionId)->exists()) {
            return redirect()->back()
                ->withErrors(['section_id' => 'Invalid section selected.'])
                ->withInput();
        }

        // Prepare update data
        $updateData = [
            'basic_salary' => $request->basic_salary,
            'allowances' => $request->allowances ?? 0,
            'section_id' => $request->section_id,
            'bank_name' => $request->bank_name,
            'account_number' => $request->account_number,
        ];

        // Handle employee_id and staff_name
        if ($request->filled('employee_id')) {
            $updateData['employee_id'] = $request->employee_id;
            $updateData['staff_name'] = null; // Clear contract name
        } else {
            $updateData['employee_id'] = null;
            $updateData['staff_name'] = $request->staff_name;
        }

        $payroll->update($updateData);

        return redirect()
            ->route('finance.payroll.index')
            ->with('success', 'Payroll updated successfully!');
    }

    public function destroy(Payroll $payroll)
    {
        $payroll->delete();

        return redirect()
            ->route('finance.payroll.index')
            ->with('success', 'Payroll deleted successfully!');
    }

    public function processedSalaries(Request $request)
    {
        $query = SalaryPayment::with(['employee', 'section', 'term', 'session', 'processedBy'])
            ->orderBy('processed_at', 'desc');

        // Apply filters
        if ($request->filled('filter_section')) {
            $query->where('section_id', $request->filter_section);
        }

        if ($request->filled('filter_term')) {
            $query->whereHas('term', function ($q) use ($request) {
                $q->where('name', $request->filter_term);
            });
        }

        if ($request->filled('filter_month')) {
            $query->where('month', $request->filter_month);
        }

        if ($request->filled('filter_year')) {
            $query->where('year', $request->filter_year);
        }

        if ($request->filled('filter_employee')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->filter_employee . '%');
            });
        }

        if ($request->filled('filter_status')) {
            $query->where('status', $request->filter_status);
        }

        $salaryPayments = $query->paginate(20);

        // Get filter options
        $sections = Section::orderBy('section_name')->get();
        $termNames = Term::select('name')->distinct()->orderBy('name')->get();
        $allTerms = Term::with('session')->orderBy('name')->get();
        $currentYear = Carbon::now('Africa/Lagos')->year;
        $years = range($currentYear - 5, $currentYear + 1);

        return view('processed_salaries', compact('salaryPayments', 'sections', 'termNames', 'allTerms', 'years'));
    }

    /**
     * Generate individual payment slip
     */
    public function generatePaymentSlip(SalaryPayment $salaryPayment)
    {
        $salaryPayment->load(['employee', 'section', 'term', 'session', 'processedBy']);

        return view('payment_slip', compact('salaryPayment'));
    }

    /**
     * Generate bulk payment slips
     */
    public function bulkPaymentSlips(Request $request)
    {
        $request->validate([
            'section_id' => 'required|exists:sections,id',
            'term_id' => 'required|exists:terms,id',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer',
        ]);

        $salaryPayments = SalaryPayment::with(['employee', 'section', 'term', 'session'])
            ->where('section_id', $request->section_id)
            ->where('term_id', $request->term_id)
            ->where('month', $request->month)
            ->where('year', $request->year)
            ->get();

        if ($salaryPayments->isEmpty()) {
            return back()->with('error', 'No salary payments found for the selected criteria.');
        }

        return view('bulk_payment_slips', compact('salaryPayments'));
    }
}
