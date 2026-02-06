<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\MiscFeePayment;
use App\Models\SalaryPayment;
use App\Models\OtherExpense;
use App\Models\Section;
use App\Models\SchoolClass;
use App\Models\FeeProspectus;
use App\Models\Session;
use App\Models\Term;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

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

    // NEW ANALYSIS METHOD
    public function analysis(Request $request)
    {
        // Get filter parameters
        $sectionId = $request->input('section_id');
        $sessionId = $request->input('session_id');
        $termId = $request->input('term_id');
        $classId = $request->input('class_id');

        // Base query for total expected fees
        $prospectusQuery = FeeProspectus::query();
        
        // Base query for total payments
        $paymentsQuery = Payment::query();

        // Apply filters
        if ($sectionId) {
            $prospectusQuery->where('section_id', $sectionId);
            $paymentsQuery->where('section_id', $sectionId);
        }

        if ($sessionId) {
            $prospectusQuery->whereHas('term', function($q) use ($sessionId) {
                $q->where('session_id', $sessionId);
            });
            $paymentsQuery->where('session_id', $sessionId);
        }

        if ($termId) {
            $prospectusQuery->where('term_id', $termId);
            $paymentsQuery->where('term_id', $termId);
        }

        if ($classId) {
            $prospectusQuery->where('class_id', $classId);
            $paymentsQuery->where('class_id', $classId);
        }

        // Calculate totals
        $totalExpected = $prospectusQuery->sum('total_amount');
        $totalPaid = $paymentsQuery->sum('amount');
        $totalOutstanding = $totalExpected - $totalPaid;

        // Collection rate percentage
        $collectionRate = $totalExpected > 0 ? ($totalPaid / $totalExpected) * 100 : 0;

        // Get breakdown by section
        $sectionBreakdown = $this->getSectionBreakdown($sectionId, $sessionId, $termId, $classId);

        // Get breakdown by session
        $sessionBreakdown = $this->getSessionBreakdown($sectionId, $sessionId, $termId, $classId);

        // Get breakdown by term
        $termBreakdown = $this->getTermBreakdown($sectionId, $sessionId, $termId, $classId);

        // Get breakdown by class
        $classBreakdown = $this->getClassBreakdown($sectionId, $sessionId, $termId, $classId);

        // Get top debtors (students with highest outstanding)
        $topDebtors = $this->getTopDebtors($sectionId, $sessionId, $termId, $classId, 10);

        // Get payment trends (monthly)
        $paymentTrends = $this->getPaymentTrends($sectionId, $sessionId, $termId, $classId);

        // Filter options
        $sections = Section::orderBy('section_name')->get();
        $sessions = Session::select('id', 'name', 'section_id')
            ->when($sectionId, function($q) use ($sectionId) {
                return $q->where('section_id', $sectionId);
            })
            ->orderBy('name', 'desc')
            ->get();
        
        $terms = Term::select('terms.id', 'terms.name', 'terms.session_id')
            ->when($sessionId, function($q) use ($sessionId) {
                return $q->where('session_id', $sessionId);
            })
            ->orderBy('name')
            ->get();

        $classes = SchoolClass::select('id', 'name', 'section_id')
            ->when($sectionId, function($q) use ($sectionId) {
                return $q->where('section_id', $sectionId);
            })
            ->orderBy('name')
            ->get();

        return view('finance.analysis', compact(
            'totalExpected',
            'totalPaid',
            'totalOutstanding',
            'collectionRate',
            'sectionBreakdown',
            'sessionBreakdown',
            'termBreakdown',
            'classBreakdown',
            'topDebtors',
            'paymentTrends',
            'sections',
            'sessions',
            'terms',
            'classes',
            'sectionId',
            'sessionId',
            'termId',
            'classId'
        ));
    }

    private function getSectionBreakdown($sectionId, $sessionId, $termId, $classId)
    {
        $query = Section::select('sections.id', 'sections.section_name')
            ->when($sectionId, function($q) use ($sectionId) {
                return $q->where('sections.id', $sectionId);
            });

        return $query->get()->map(function($section) use ($sessionId, $termId, $classId) {
            $prospectusQuery = FeeProspectus::where('section_id', $section->id);
            $paymentsQuery = Payment::where('section_id', $section->id);

            if ($sessionId) {
                $prospectusQuery->whereHas('term', function($q) use ($sessionId) {
                    $q->where('session_id', $sessionId);
                });
                $paymentsQuery->where('session_id', $sessionId);
            }

            if ($termId) {
                $prospectusQuery->where('term_id', $termId);
                $paymentsQuery->where('term_id', $termId);
            }

            if ($classId) {
                $prospectusQuery->where('class_id', $classId);
                $paymentsQuery->where('class_id', $classId);
            }

            $expected = $prospectusQuery->sum('total_amount');
            $paid = $paymentsQuery->sum('amount');

            return [
                'name' => $section->section_name,
                'expected' => $expected,
                'paid' => $paid,
                'outstanding' => $expected - $paid,
                'rate' => $expected > 0 ? ($paid / $expected) * 100 : 0
            ];
        })->filter(function($item) {
            return $item['expected'] > 0;
        });
    }

    private function getSessionBreakdown($sectionId, $sessionId, $termId, $classId)
    {
        $query = Session::select('school_sessions.id', 'school_sessions.name', 'school_sessions.section_id')
            ->when($sectionId, function($q) use ($sectionId) {
                return $q->where('section_id', $sectionId);
            })
            ->when($sessionId, function($q) use ($sessionId) {
                return $q->where('school_sessions.id', $sessionId);
            });

        return $query->get()->map(function($session) use ($sectionId, $termId, $classId) {
            $prospectusQuery = FeeProspectus::whereHas('term', function($q) use ($session) {
                $q->where('session_id', $session->id);
            });
            $paymentsQuery = Payment::where('session_id', $session->id);

            if ($sectionId) {
                $prospectusQuery->where('section_id', $sectionId);
                $paymentsQuery->where('section_id', $sectionId);
            }

            if ($termId) {
                $prospectusQuery->where('term_id', $termId);
                $paymentsQuery->where('term_id', $termId);
            }

            if ($classId) {
                $prospectusQuery->where('class_id', $classId);
                $paymentsQuery->where('class_id', $classId);
            }

            $expected = $prospectusQuery->sum('total_amount');
            $paid = $paymentsQuery->sum('amount');

            return [
                'name' => $session->name,
                'expected' => $expected,
                'paid' => $paid,
                'outstanding' => $expected - $paid,
                'rate' => $expected > 0 ? ($paid / $expected) * 100 : 0
            ];
        })->filter(function($item) {
            return $item['expected'] > 0;
        });
    }

    private function getTermBreakdown($sectionId, $sessionId, $termId, $classId)
    {
        $query = Term::select('terms.id', 'terms.name', 'terms.session_id')
            ->with('session')
            ->when($sessionId, function($q) use ($sessionId) {
                return $q->where('session_id', $sessionId);
            })
            ->when($termId, function($q) use ($termId) {
                return $q->where('terms.id', $termId);
            });

        return $query->get()->map(function($term) use ($sectionId, $classId) {
            $prospectusQuery = FeeProspectus::where('term_id', $term->id);
            $paymentsQuery = Payment::where('term_id', $term->id);

            if ($sectionId) {
                $prospectusQuery->where('section_id', $sectionId);
                $paymentsQuery->where('section_id', $sectionId);
            }

            if ($classId) {
                $prospectusQuery->where('class_id', $classId);
                $paymentsQuery->where('class_id', $classId);
            }

            $expected = $prospectusQuery->sum('total_amount');
            $paid = $paymentsQuery->sum('amount');

            return [
                'name' => $term->name . ' (' . ($term->session->name ?? 'N/A') . ')',
                'expected' => $expected,
                'paid' => $paid,
                'outstanding' => $expected - $paid,
                'rate' => $expected > 0 ? ($paid / $expected) * 100 : 0
            ];
        })->filter(function($item) {
            return $item['expected'] > 0;
        });
    }

    private function getClassBreakdown($sectionId, $sessionId, $termId, $classId)
    {
        $query = SchoolClass::select('school_classes.id', 'school_classes.name', 'school_classes.section_id')
            ->when($sectionId, function($q) use ($sectionId) {
                return $q->where('section_id', $sectionId);
            })
            ->when($classId, function($q) use ($classId) {
                return $q->where('school_classes.id', $classId);
            });

        return $query->get()->map(function($class) use ($sessionId, $termId) {
            $prospectusQuery = FeeProspectus::where('class_id', $class->id);
            $paymentsQuery = Payment::where('class_id', $class->id);

            if ($sessionId) {
                $prospectusQuery->whereHas('term', function($q) use ($sessionId) {
                    $q->where('session_id', $sessionId);
                });
                $paymentsQuery->where('session_id', $sessionId);
            }

            if ($termId) {
                $prospectusQuery->where('term_id', $termId);
                $paymentsQuery->where('term_id', $termId);
            }

            $expected = $prospectusQuery->sum('total_amount');
            $paid = $paymentsQuery->sum('amount');

            return [
                'name' => $class->name,
                'expected' => $expected,
                'paid' => $paid,
                'outstanding' => $expected - $paid,
                'rate' => $expected > 0 ? ($paid / $expected) * 100 : 0
            ];
        })->filter(function($item) {
            return $item['expected'] > 0;
        });
    }

    private function getTopDebtors($sectionId, $sessionId, $termId, $classId, $limit = 10)
    {
        // Get all students with fee prospectus
        $studentsQuery = User::where('user_type', 4)
            ->when($sectionId, function($q) use ($sectionId) {
                return $q->where('section_id', $sectionId);
            })
            ->when($classId, function($q) use ($classId) {
                return $q->where('class_id', $classId);
            })
            ->with(['section', 'schoolClass']);

        $students = $studentsQuery->get()->map(function($student) use ($sessionId, $termId) {
            // Calculate expected fees
            $prospectusQuery = FeeProspectus::where('section_id', $student->section_id)
                ->where('class_id', $student->class_id);

            if ($sessionId) {
                $prospectusQuery->whereHas('term', function($q) use ($sessionId) {
                    $q->where('session_id', $sessionId);
                });
            }

            if ($termId) {
                $prospectusQuery->where('term_id', $termId);
            }

            $totalExpected = $prospectusQuery->sum('total_amount');

            // Calculate payments
            $paymentsQuery = Payment::where('student_id', $student->id);

            if ($sessionId) {
                $paymentsQuery->where('session_id', $sessionId);
            }

            if ($termId) {
                $paymentsQuery->where('term_id', $termId);
            }

            $totalPaid = $paymentsQuery->sum('amount');
            $outstanding = $totalExpected - $totalPaid;

            return [
                'student_id' => $student->id,
                'name' => $student->name,
                'admission_no' => $student->admission_no,
                'class' => $student->schoolClass->name ?? 'N/A',
                'section' => $student->section->section_name ?? 'N/A',
                'expected' => $totalExpected,
                'paid' => $totalPaid,
                'outstanding' => $outstanding
            ];
        })->filter(function($item) {
            return $item['outstanding'] > 0;
        })->sortByDesc('outstanding')->take($limit);

        return $students;
    }

    private function getPaymentTrends($sectionId, $sessionId, $termId, $classId)
    {
        $query = Payment::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('SUM(amount) as total')
        )
        ->when($sectionId, function($q) use ($sectionId) {
            return $q->where('section_id', $sectionId);
        })
        ->when($sessionId, function($q) use ($sessionId) {
            return $q->where('session_id', $sessionId);
        })
        ->when($termId, function($q) use ($termId) {
            return $q->where('term_id', $termId);
        })
        ->when($classId, function($q) use ($classId) {
            return $q->where('class_id', $classId);
        })
        ->where('created_at', '>=', now()->subMonths(12))
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        return $query;
    }

    public function exportAnalysis(Request $request)
    {
        // Get filter parameters
        $sectionId = $request->input('section_id');
        $sessionId = $request->input('session_id');
        $termId = $request->input('term_id');
        $classId = $request->input('class_id');

        // Reuse analysis logic
        $prospectusQuery = FeeProspectus::query();
        $paymentsQuery = Payment::query();

        if ($sectionId) {
            $prospectusQuery->where('section_id', $sectionId);
            $paymentsQuery->where('section_id', $sectionId);
        }

        if ($sessionId) {
            $prospectusQuery->whereHas('term', function($q) use ($sessionId) {
                $q->where('session_id', $sessionId);
            });
            $paymentsQuery->where('session_id', $sessionId);
        }

        if ($termId) {
            $prospectusQuery->where('term_id', $termId);
            $paymentsQuery->where('term_id', $termId);
        }

        if ($classId) {
            $prospectusQuery->where('class_id', $classId);
            $paymentsQuery->where('class_id', $classId);
        }

        $totalExpected = $prospectusQuery->sum('total_amount');
        $totalPaid = $paymentsQuery->sum('amount');
        $totalOutstanding = $totalExpected - $totalPaid;
        $collectionRate = $totalExpected > 0 ? ($totalPaid / $totalExpected) * 100 : 0;

        $sectionBreakdown = $this->getSectionBreakdown($sectionId, $sessionId, $termId, $classId);
        $sessionBreakdown = $this->getSessionBreakdown($sectionId, $sessionId, $termId, $classId);
        $termBreakdown = $this->getTermBreakdown($sectionId, $sessionId, $termId, $classId);
        $classBreakdown = $this->getClassBreakdown($sectionId, $sessionId, $termId, $classId);
        $topDebtors = $this->getTopDebtors($sectionId, $sessionId, $termId, $classId, 10);

        $pdf = Pdf::loadView('finance.analysis_export', compact(
            'totalExpected',
            'totalPaid',
            'totalOutstanding',
            'collectionRate',
            'sectionBreakdown',
            'sessionBreakdown',
            'termBreakdown',
            'classBreakdown',
            'topDebtors',
            'sectionId',
            'sessionId',
            'termId',
            'classId'
        ));
        
        return $pdf->download('financial-analysis-' . now()->format('Y-m-d') . '.pdf');
    }
}