<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\MiscFeePayment;
use App\Models\SalaryPayment;
use App\Models\OtherExpense;
use App\Models\Section;
use App\Models\SchoolClass;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class FinanceReportController extends Controller
{
    public function overview(Request $request)
    {
        $perPage = 20;
        $currentPage = $request->get('page', 1);
        $filters = $request->only(['filter_date_from', 'filter_date_to', 'filter_section', 'filter_type', 'filter_search']);

        // Fetch income: Student Payments
        $studentPaymentsQuery = Payment::with(['student', 'section', 'term.session'])
            ->where('amount', '>', 0);

        if ($request->filled('filter_date_from')) {
            $studentPaymentsQuery->whereDate('created_at', '>=', $request->filter_date_from);
        }
        if ($request->filled('filter_date_to')) {
            $studentPaymentsQuery->whereDate('created_at', '<=', $request->filter_date_to);
        }
        if ($request->filled('filter_section')) {
            $studentPaymentsQuery->where('section_id', $request->filter_section);
        }
        if ($request->filled('filter_search')) {
            $studentPaymentsQuery->whereHas('student', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->filter_search . '%')
                  ->orWhere('admission_no', 'like', '%' . $request->filter_search . '%');
            });
        }
        if ($request->filled('filter_type') && $request->filter_type === 'expense') {
            $studentPaymentsQuery->whereRaw('1 = 0'); // Exclude if filtering expenses only
        }

        $studentPayments = $studentPaymentsQuery->orderBy('created_at', 'desc')->get();

        // Transform to common format
        $incomeStudent = $studentPayments->map(function ($payment) {
            return [
                'id' => $payment->id,
                'date' => $payment->created_at->format('Y-m-d'),
                'type' => 'income',
                'subtype' => 'Student Fee Payment',
                'description' => $payment->student->name ?? 'N/A',
                'amount' => $payment->amount,
                'section_id' => $payment->section_id,
                'section_name' => $payment->section->section_name ?? 'N/A',
                'model' => 'Payment'
            ];
        });

        // Fetch income: Misc Fee Payments
        $miscPaymentsQuery = MiscFeePayment::with(['student.schoolClass.section', 'miscFeeType'])
            ->where('amount_paid', '>', 0)
            ->where('status', 'paid'); // Only paid ones as income

        if ($request->filled('filter_date_from')) {
            $miscPaymentsQuery->whereDate('payment_date', '>=', $request->filter_date_from);
        }
        if ($request->filled('filter_date_to')) {
            $miscPaymentsQuery->whereDate('payment_date', '<=', $request->filter_date_to);
        }
        if ($request->filled('filter_section')) {
            $miscPaymentsQuery->whereHas('student.schoolClass.section', function ($q) use ($request) {
                $q->where('id', $request->filter_section);
            });
        }
        if ($request->filled('filter_search')) {
            $miscPaymentsQuery->whereHas('student', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->filter_search . '%')
                  ->orWhere('admission_no', 'like', '%' . $request->filter_search . '%');
            });
        }
        if ($request->filled('filter_type') && $request->filter_type === 'expense') {
            $miscPaymentsQuery->whereRaw('1 = 0'); // Exclude
        }

        $miscPayments = $miscPaymentsQuery->orderBy('payment_date', 'desc')->get();

        $incomeMisc = $miscPayments->map(function ($payment) {
            return [
                'id' => $payment->id,
                'date' => $payment->payment_date->format('Y-m-d'),
                'type' => 'income',
                'subtype' => 'Miscellaneous Fee',
                'description' => ($payment->student->name ?? 'N/A') . ' - ' . ($payment->miscFeeType->name ?? 'N/A'),
                'amount' => $payment->amount_paid,
                'section_id' => $payment->student->schoolClass->section->id ?? null,
                'section_name' => $payment->student->schoolClass->section->section_name ?? 'N/A',
                'model' => 'MiscFeePayment'
            ];
        });

        // Fetch expenses: Salary Payments
        $salaryQuery = SalaryPayment::with(['employee', 'section'])
            ->where('net_pay', '>', 0)
            ->where('status', 'processed');

        if ($request->filled('filter_date_from')) {
            $salaryQuery->whereYear('processed_at', '>=', explode('-', $request->filter_date_from)[0])
                        ->whereMonth('processed_at', '>=', explode('-', $request->filter_date_from)[1]);
        }
        if ($request->filled('filter_date_to')) {
            $salaryQuery->whereYear('processed_at', '<=', explode('-', $request->filter_date_to)[0])
                        ->whereMonth('processed_at', '<=', explode('-', $request->filter_date_to)[1]);
        }
        if ($request->filled('filter_section')) {
            $salaryQuery->where('section_id', $request->filter_section);
        }
        if ($request->filled('filter_search')) {
            $salaryQuery->whereHas('employee', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->filter_search . '%');
            });
        }
        if ($request->filled('filter_type') && $request->filter_type === 'income') {
            $salaryQuery->whereRaw('1 = 0'); // Exclude
        }

        $salaries = $salaryQuery->orderBy('processed_at', 'desc')->get();

        $expenseSalary = $salaries->map(function ($payment) {
            return [
                'id' => $payment->id,
                'date' => $payment->processed_at->format('Y-m-d'),
                'type' => 'expense',
                'subtype' => 'Salary Payment',
                'description' => $payment->employee->name ?? 'N/A',
                'amount' => $payment->net_pay,
                'section_id' => $payment->section_id,
                'section_name' => $payment->section->section_name ?? 'N/A',
                'model' => 'SalaryPayment'
            ];
        });

        // Fetch expenses: Other Expenses
        $otherQuery = OtherExpense::with('section')
            ->where('amount', '>', 0);

        if ($request->filled('filter_date_from')) {
            $otherQuery->whereDate('created_at', '>=', $request->filter_date_from);
        }
        if ($request->filled('filter_date_to')) {
            $otherQuery->whereDate('created_at', '<=', $request->filter_date_to);
        }
        if ($request->filled('filter_section')) {
            $otherQuery->where('section_id', $request->filter_section);
        }
        if ($request->filled('filter_search')) {
            $otherQuery->where('description', 'like', '%' . $request->filter_search . '%');
        }
        if ($request->filled('filter_type') && $request->filter_type === 'income') {
            $otherQuery->whereRaw('1 = 0'); // Exclude
        }

        $others = $otherQuery->orderBy('created_at', 'desc')->get();

        $expenseOther = $others->map(function ($expense) {
            return [
                'id' => $expense->id,
                'date' => $expense->created_at->format('Y-m-d'),
                'type' => 'expense',
                'subtype' => 'Other Expense',
                'description' => $expense->description,
                'amount' => $expense->amount,
                'section_id' => $expense->section_id,
                'section_name' => $expense->section_id ? ($expense->section->section_name ?? 'N/A') : 'All Sections',
                'model' => 'OtherExpense'
            ];
        });

        // Merge all
        $allTransactions = $incomeStudent->merge($incomeMisc)->merge($expenseSalary)->merge($expenseOther)
            ->sortByDesc('date')
            ->values();

        // Manual pagination
        $total = $allTransactions->count();
        $paginated = (new LengthAwarePaginator(
            $allTransactions->forPage($currentPage, $perPage),
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'pageName' => 'page']
        ))->appends($filters);

        // Calculate totals
        $totalIncome = $incomeStudent->sum('amount') + $incomeMisc->sum('amount');
        $totalExpense = $expenseSalary->sum('amount') + $expenseOther->sum('amount');
        $net = $totalIncome - $totalExpense;

        // Filter options
        $sections = Section::orderBy('section_name')->get();
        $types = ['all' => 'All', 'income' => 'Income', 'expense' => 'Expense'];

        return view('finance_overview', compact('paginated', 'filters', 'sections', 'types', 'totalIncome', 'totalExpense', 'net'));
    }
}