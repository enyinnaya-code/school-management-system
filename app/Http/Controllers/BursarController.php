<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use App\Models\Section;
use App\Models\SchoolClass;
use App\Models\User;
use App\Models\Session;
use App\Models\Term;
use App\Models\FeeProspectus;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;



class BursarController extends Controller
{

    public function selectStudentForPayment(Request $request)
    {
        $request->validate([
            'section_id' => 'required|exists:sections,id',
            'class_id' => 'required|exists:school_classes,id',
            'student_id' => 'required|exists:users,id',
        ]);

        $url = URL::temporarySignedRoute(
            'bursar.payment.details.signed',
            now()->addHour(), // expires in 1 hour
            [
                'studentId' => $request->student_id,
                'sectionId' => $request->section_id,
                'classId' => $request->class_id,
            ]
        );

        return redirect($url);
    }


    public function dashboard()
    {
        // Current date
        $today = Carbon::today();

        // Current Session & Term (global – assuming one current across all sections)
        $currentSession = Session::where('is_current', true)->first(['id', 'name']);
        $currentTerm    = Term::where('is_current', true)->first(['id', 'name']);

        // Total students & teachers (for quick context)
        $totalStudents = User::where('user_type', 4)->count();
        $totalTeachers = User::where('user_type', 3)->count();

        // Today's revenue
        $todayRevenue = Payment::whereDate('created_at', $today)->sum('amount');

        // Current term revenue
        $termRevenue = 0;
        if ($currentTerm && $currentSession) {
            $termRevenue = Payment::where('term_id', $currentTerm->id)
                ->where('session_id', $currentSession->id)
                ->sum('amount');
        }

        // Total expected fees for current term (sum of all prospectus total_amount)
        $totalExpected = 0;
        if ($currentTerm) {
            $totalExpected = FeeProspectus::where('term_id', $currentTerm->id)
                ->sum('total_amount');
        }

        // Outstanding = expected - paid in current term
        $outstanding = $totalExpected - $termRevenue;

        // Recent payments (last 10)
        $recentPayments = Payment::with(['student', 'section', 'schoolClass'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Monthly revenue trend for chart (last 6 months)
        $monthlyRevenue = Payment::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('SUM(amount) as total')
        )
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $revenueLabels = $monthlyRevenue->keys()->map(function ($date) {
            return Carbon::createFromFormat('Y-m', $date)->format('M Y');
        });

        $revenueData = $monthlyRevenue->values();

        return view('bursar.dashboard', compact(
            'currentSession',
            'currentTerm',
            'totalStudents',
            'totalTeachers',
            'todayRevenue',
            'termRevenue',
            'outstanding',
            'totalExpected',
            'recentPayments',
            'revenueLabels',
            'revenueData'
        ));
    }

    public function createPayment()
    {
        $sections = Section::all();
        return view('create_new_bursary', compact('sections'));
    }

    public function getClassesBySection($sectionId)
    {
        $classes = SchoolClass::where('section_id', $sectionId)
            ->orderBy('name', 'asc')
            ->get(['id', 'name']);
        return response()->json($classes);
    }

    public function getStudentsByClass($classId)
    {
        $students = User::where('user_type', 4)
            ->where('class_id', $classId)
            ->select('id', 'name', 'admission_no')
            ->get();
        return response()->json($students);
    }

    private function getCurrentTerm($sectionId)
    {
        $session = Session::where('section_id', $sectionId)
            ->where('is_current', true)
            ->first();

        if ($session) {
            return Term::where('session_id', $session->id)
                ->where('is_current', true)
                ->first();
        }

        return null;
    }

    public function paymentDetails(int $studentId, int $sectionId, int $classId, Request $request)
    {
        // Remove the section check from the student query
        $student = User::where('id', $studentId)
            ->where('user_type', 4)
            ->where('class_id', $classId)
            ->firstOrFail();

        $section = Section::findOrFail($sectionId);
        $class = SchoolClass::findOrFail($classId);
        $currentTerm = $this->getCurrentTerm($sectionId);

        if (!$currentTerm) {
            return redirect()->route('payment.create')->with('error', 'No current term found for this section.');
        }

        $currentTerm->load('session');

        if (!$currentTerm->session) {
            return redirect()->route('payment.create')->with('error', 'No valid session found for the current term.');
        }

        $sessionId = $currentTerm->session->id;
        $currentSession = $currentTerm->session;

        $prospectus = FeeProspectus::where('section_id', $sectionId)
            ->where('class_id', $classId)
            ->where('term_id', $currentTerm->id)
            ->first();

        $totalDue = $prospectus ? $prospectus->total_amount : 0;

        $paymentsQuery = Payment::where('student_id', $studentId)
            ->where('section_id', $sectionId)
            ->where('class_id', $classId)
            ->where('term_id', $currentTerm->id)
            ->where('session_id', $sessionId)
            ->orderBy('created_at', 'desc');

        $payments = $paymentsQuery->paginate(15);
        $payments->onEachSide(0);
        $paid = $paymentsQuery->sum('amount');
        $balance = $totalDue - $paid;

        // Fetch previous outstanding balances
        $previousBalances = collect();

        // Past terms in current session
        $pastTermsCurrent = Term::where('session_id', $currentSession->id)
            ->where('is_current', false)
            ->get();

        foreach ($pastTermsCurrent as $pastTerm) {
            $pastProspectus = FeeProspectus::where('section_id', $sectionId)
                ->where('class_id', $classId)
                ->where('term_id', $pastTerm->id)
                ->first();

            if ($pastProspectus) {
                $pastPaymentsSum = Payment::where('student_id', $studentId)
                    ->where('section_id', $sectionId)
                    ->where('class_id', $classId)
                    ->where('term_id', $pastTerm->id)
                    ->where('session_id', $currentSession->id)
                    ->sum('amount');

                $pastBalance = $pastProspectus->total_amount - $pastPaymentsSum;

                if ($pastBalance > 0) {
                    $previousBalances->push([
                        'term_name' => $pastTerm->name,
                        'session_name' => $currentSession->name,
                        'total' => $pastProspectus->total_amount,
                        'paid' => $pastPaymentsSum,
                        'balance' => $pastBalance
                    ]);
                }
            }
        }

        // Past sessions
        $pastSessions = Session::where('section_id', $sectionId)
            ->where('is_current', false)
            ->get();

        foreach ($pastSessions as $pastSession) {
            $pastTerms = Term::where('session_id', $pastSession->id)
                ->get();

            foreach ($pastTerms as $pastTerm) {
                $pastProspectus = FeeProspectus::where('section_id', $sectionId)
                    ->where('class_id', $classId)
                    ->where('term_id', $pastTerm->id)
                    ->first();

                if ($pastProspectus) {
                    $pastPaymentsSum = Payment::where('student_id', $studentId)
                        ->where('section_id', $sectionId)
                        ->where('class_id', $classId)
                        ->where('term_id', $pastTerm->id)
                        ->where('session_id', $pastSession->id)
                        ->sum('amount');

                    $pastBalance = $pastProspectus->total_amount - $pastPaymentsSum;

                    if ($pastBalance > 0) {
                        $previousBalances->push([
                            'term_name' => $pastTerm->name,
                            'session_name' => $pastSession->name,
                            'total' => $pastProspectus->total_amount,
                            'paid' => $pastPaymentsSum,
                            'balance' => $pastBalance
                        ]);
                    }
                }
            }
        }

        // Sort by session and term
        $previousBalances = $previousBalances->sortByDesc(function ($item) {
            return $item['session_name'] . $item['term_name'];
        });

        return view('payment_details', compact(
            'student',
            'section',
            'class',
            'currentTerm',
            'prospectus',
            'totalDue',
            'paid',
            'balance',
            'payments',
            'sessionId',
            'previousBalances'
        ));
    }


    private function generateReceiptPdf(Payment $payment)
    {
        $student = User::findOrFail($payment->student_id);
        $section = Section::findOrFail($payment->section_id);
        $class = SchoolClass::findOrFail($payment->class_id);
        $currentTerm = Term::findOrFail($payment->term_id);
        $session = Session::findOrFail($payment->session_id);

        $prospectus = FeeProspectus::where('section_id', $payment->section_id)
            ->where('class_id', $payment->class_id)
            ->where('term_id', $payment->term_id)
            ->first();

        $totalDue = $prospectus?->total_amount ?? 0;

        $allPaymentsSum = Payment::where('student_id', $payment->student_id)
            ->where('term_id', $payment->term_id)
            ->where('session_id', $payment->session_id)
            ->sum('amount');

        $balance = $totalDue - $allPaymentsSum;

        $pdf = Pdf::loadView('payment_receipt', compact(
            'payment',
            'student',
            'section',
            'class',
            'currentTerm',
            'session',
            'totalDue',
            'balance'
        ));

        $pdf->setPaper([0, 0, 227, 500], 'mm');

        $filename = 'payment-receipt-' . $student->admission_no . '-' . $payment->id . '.pdf';

        return $pdf->stream($filename);
    }


    public function processPayment(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'section_id' => 'required|exists:sections,id',
            'class_id' => 'required|exists:school_classes,id',
            'term_id' => 'required|exists:terms,id',
            'session_id' => 'required|exists:school_sessions,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_type' => 'required|in:Cash,Bank Transfer,Online Payment,Cheque',
            'description' => 'nullable|string|max:500',
        ]);

        // Generate a one-time idempotency key stored in session
        $paymentKey = $request->session()->get('current_payment_key');

        if (!$paymentKey) {
            $paymentKey = Str::uuid()->toString();
            $request->session()->put('current_payment_key', $paymentKey);
        }

        return DB::transaction(function () use ($request, $paymentKey) {
            // Prevent duplicate if already processed in this session
            $existing = Payment::where('idempotency_key', $paymentKey)->first();
            if ($existing) {
                $request->session()->forget('current_payment_key'); // Clean up
                return redirect()->route('bursar.payment.receipt', $existing)
                    ->with('info', 'This payment was already recorded.');
            }

            $payment = Payment::create([
                'student_id' => $request->student_id,
                'section_id' => $request->section_id,
                'class_id' => $request->class_id,
                'term_id' => $request->term_id,
                'session_id' => $request->session_id,
                'amount' => $request->amount,
                'payment_type' => $request->payment_type,
                'description' => $request->description,
                'created_by' => Auth::id(),
                'idempotency_key' => $paymentKey,
            ]);

            // Critical: Clear the session key after successful creation
            $request->session()->forget('current_payment_key');

            // return redirect()->route('bursar.payment.receipt', $payment)
            //     ->with('success', 'Payment recorded successfully! Printing receipt...');

            // Right after creating the payment and before redirecting
            session()->flash('just_paid', true);

            // Then redirect to receipt
            return redirect()->route('bursar.payment.receipt', $payment)
                ->with('success', 'Payment recorded successfully!');
        });
    }


    public function managePayments(Request $request)
    {
        $query = Payment::with(['student', 'section', 'schoolClass', 'term.session', 'createdBy']);

        // Optional filters (you can expand this based on needs, similar to manageFeeProspectus)
        if ($request->filled('filter_section')) {
            $query->where('section_id', $request->filter_section);
        }
        if ($request->filled('filter_term')) {
            $query->where('term_id', $request->filter_term);
        }
        if ($request->filled('filter_session')) {
            $query->where('session_id', $request->filter_session);
        }
        if ($request->filled('filter_student')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->filter_student . '%')
                    ->orWhere('admission_no', 'like', '%' . $request->filter_student . '%');
            });
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate(20);
        $payments->onEachSide(0);

        // Fetch filter options
        $sections = Section::select('id', 'section_name')->orderBy('section_name')->get();
        $terms = Term::selectRaw('MIN(id) as id, name')
            ->groupBy('name')
            ->orderBy('name')
            ->get();;
        $sessions = Session::selectRaw('MIN(id) as id, name')
            ->groupBy('name')
            ->orderBy('name')
            ->get();
        $students = User::where('user_type', 4)->select('id', 'name', 'admission_no')->orderBy('name')->limit(50)->get(); // Limit to avoid overload

        return view('manage_payments', compact('payments', 'sections', 'terms', 'sessions', 'students'));
    }


    public function viewTransactionHistory(Request $request, $studentId)
    {
        $student = User::where('id', $studentId)->where('user_type', 4)->firstOrFail();

        $query = Payment::where('student_id', $studentId)
            ->with(['section', 'schoolClass', 'term.session', 'createdBy']);

        if ($request->filled('filter_section')) {
            $query->where('section_id', $request->filter_section);
        }
        if ($request->filled('filter_term')) {
            $query->where('term_id', $request->filter_term);
        }
        if ($request->filled('filter_session')) {
            $query->where('session_id', $request->filter_session);
        }
        if ($request->filled('filter_date_from')) {
            $query->whereDate('created_at', '>=', $request->filter_date_from);
        }
        if ($request->filled('filter_date_to')) {
            $query->whereDate('created_at', '<=', $request->filter_date_to);
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate(20);

        // Fetch filter options
        $sections = Section::select('id', 'section_name')->orderBy('section_name')->get();
        $terms = Term::selectRaw('MIN(id) as id, name')
            ->groupBy('name')
            ->orderBy('name')
            ->get();
        $sessions = Session::selectRaw('MIN(id) as id, name')
            ->groupBy('name')
            ->orderBy('name')
            ->get();

        return view('transaction_history', compact('student', 'payments', 'sections', 'terms', 'sessions'));
    }
    // Add these methods to your BursarController class
    public function editPayment(Payment $payment)
    {
        // Authorize or check permissions if needed

        $student = User::where('id', $payment->student_id)
            ->where('user_type', 4)
            ->firstOrFail();

        $section = Section::findOrFail($payment->section_id);
        $class = SchoolClass::findOrFail($payment->class_id);
        $currentTerm = Term::findOrFail($payment->term_id);
        $session = Session::findOrFail($payment->session_id);

        $currentTerm->load('session');

        $prospectus = FeeProspectus::where('section_id', $payment->section_id)
            ->where('class_id', $payment->class_id)
            ->where('term_id', $payment->term_id)
            ->first();

        $totalDue = $prospectus ? $prospectus->total_amount : 0;

        $paymentsQuery = Payment::where('student_id', $student->id)
            ->where('section_id', $payment->section_id)
            ->where('class_id', $payment->class_id)
            ->where('term_id', $payment->term_id)
            ->where('session_id', $payment->session_id)
            ->orderBy('created_at', 'desc');

        $payments = $paymentsQuery->paginate(15);
        $payments->onEachSide(0);
        $paid = $paymentsQuery->sum('amount');
        $balance = $totalDue - $paid;

        // Fetch previous outstanding balances (reuse the logic from paymentDetails)
        $previousBalances = collect();

        // Past terms in current session
        $pastTermsCurrent = Term::where('session_id', $session->id)
            ->where('is_current', false)
            ->get();

        foreach ($pastTermsCurrent as $pastTerm) {
            $pastProspectus = FeeProspectus::where('section_id', $payment->section_id)
                ->where('class_id', $payment->class_id)
                ->where('term_id', $pastTerm->id)
                ->first();

            if ($pastProspectus) {
                $pastPaymentsSum = Payment::where('student_id', $student->id)
                    ->where('section_id', $payment->section_id)
                    ->where('class_id', $payment->class_id)
                    ->where('term_id', $pastTerm->id)
                    ->where('session_id', $session->id)
                    ->sum('amount');

                $pastBalance = $pastProspectus->total_amount - $pastPaymentsSum;

                if ($pastBalance > 0) {
                    $previousBalances->push([
                        'term_name' => $pastTerm->name,
                        'session_name' => $session->name,
                        'total' => $pastProspectus->total_amount,
                        'paid' => $pastPaymentsSum,
                        'balance' => $pastBalance
                    ]);
                }
            }
        }

        // Past sessions
        $pastSessions = Session::where('section_id', $payment->section_id)
            ->where('is_current', false)
            ->get();

        foreach ($pastSessions as $pastSession) {
            $pastTerms = Term::where('session_id', $pastSession->id)
                ->get();

            foreach ($pastTerms as $pastTerm) {
                $pastProspectus = FeeProspectus::where('section_id', $payment->section_id)
                    ->where('class_id', $payment->class_id)
                    ->where('term_id', $pastTerm->id)
                    ->first();

                if ($pastProspectus) {
                    $pastPaymentsSum = Payment::where('student_id', $student->id)
                        ->where('section_id', $payment->section_id)
                        ->where('class_id', $payment->class_id)
                        ->where('term_id', $pastTerm->id)
                        ->where('session_id', $pastSession->id)
                        ->sum('amount');

                    $pastBalance = $pastProspectus->total_amount - $pastPaymentsSum;

                    if ($pastBalance > 0) {
                        $previousBalances->push([
                            'term_name' => $pastTerm->name,
                            'session_name' => $pastSession->name,
                            'total' => $pastProspectus->total_amount,
                            'paid' => $pastPaymentsSum,
                            'balance' => $pastBalance
                        ]);
                    }
                }
            }
        }

        // Sort by session and term (assuming higher id is more recent)
        $previousBalances = $previousBalances->sortByDesc(function ($item) {
            return $item['session_name'] . $item['term_name'];
        });

        return view('edit_payment', compact(
            'payment',
            'student',
            'section',
            'class',
            'currentTerm',
            'session',
            'prospectus',
            'totalDue',
            'paid',
            'balance',
            'previousBalances',
            'payments'
        ));
    }

    public function updatePayment(Request $request, Payment $payment)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_type' => 'required|in:Cash,Bank Transfer,Online Payment,Cheque',
            'description' => 'nullable|string|max:500',
        ]);

        $payment->update([
            'amount' => $request->amount,
            'payment_type' => $request->payment_type,
            'description' => $request->description,
            'updated_by' => Auth::id(), // Assuming you add an updated_by field to payments table
        ]);

        return redirect()->route('payment.manage')->with('success', 'Payment updated successfully.');
    }

    // Add this method for handling the form submission (implement as needed)
    public function storePayment(Request $request)
    {
        // Validate and process payment creation here
        $request->validate([
            'section_id' => 'required|exists:sections,id',
            'class_id' => 'required|exists:school_classes,id',
            'student_id' => 'required|exists:users,id',
            // Add other payment fields as needed
        ]);

        // Logic to create payment...

        return redirect()->back()->with('success', 'Payment created successfully.');
    }

    public function createFeeProspectus()
    {
        $sections = Section::all();
        return view('create_fee_prospectus', compact('sections'));
    }

    public function getTermsBySection($sectionId)
    {
        $session = Session::where('section_id', $sectionId)
            ->where('is_current', true)
            ->first();

        $terms = [];
        if ($session) {
            $terms = Term::where('session_id', $session->id)
                ->where('is_current', true)
                ->select('id', 'name')
                ->get();
        }

        return response()->json($terms);
    }

    public function selectForProspectus(Request $request)
    {
        $request->validate([
            'section_id' => 'required|exists:sections,id',
            'class_ids' => 'required|array|min:1',
            'class_ids.*' => 'exists:school_classes,id',
            'term_id' => 'required|exists:terms,id',
        ]);

        $section = Section::findOrFail($request->section_id);
        $classes = SchoolClass::whereIn('id', $request->class_ids)->get();
        $term = Term::findOrFail($request->term_id);

        return view('create_fee_prospectus_details', compact('section', 'classes', 'term'));
    }

    public function storeFeeProspectus(Request $request)
    {
        $request->validate([
            'section_id' => 'required|exists:sections,id',
            'class_ids' => 'required|array|min:1',
            'class_ids.*' => 'exists:school_classes,id',
            'term_id' => 'required|exists:terms,id',
            'fees' => 'required|array|min:1',
            'fees.*.item' => 'required|string|max:255',
            'fees.*.amount' => 'required|numeric|min:0',
        ]);

        // Prepare items array and calculate total
        $items = [];
        $totalAmount = 0;
        foreach ($request->fees as $fee) {
            $items[] = [
                'item' => $fee['item'],
                'amount' => $fee['amount'],
            ];
            $totalAmount += $fee['amount'];
        }

        // Create prospectus for each selected class
        foreach ($request->class_ids as $classId) {
            // Delete existing prospectus for this section/class/term if needed
            FeeProspectus::where('section_id', $request->section_id)
                ->where('class_id', $classId)
                ->where('term_id', $request->term_id)
                ->delete();

            // Create new prospectus for this class
            FeeProspectus::create([
                'section_id' => $request->section_id,
                'class_id' => $classId,
                'term_id' => $request->term_id,
                'items' => $items,
                'total_amount' => $totalAmount,
                'created_by' => Auth::id(),
            ]);
        }

        return redirect()->route('fee.prospectus.create')->with('success', 'Fee Prospectus created successfully for selected classes.');
    }

    public function manageFeeProspectus(Request $request)
    {
        $query = FeeProspectus::with(['section', 'schoolClass', 'term.session']);

        if ($request->filled('filter_section')) {
            $query->where('section_id', $request->filter_section);
        }

        if ($request->filled('filter_class')) {
            $query->where('class_id', $request->filter_class);
        }

        if ($request->filled('filter_term')) {
            $query->whereHas('term', function ($q) use ($request) {
                $q->where('name', $request->filter_term);
            });
        }

        if ($request->filled('filter_session')) {
            $query->whereHas('term.session', function ($q) use ($request) {
                $q->where('name', $request->filter_session);
            });
        }

        $prospectuses = $query->orderBy('created_at', 'desc')->paginate(10);

        $sections = Section::select('id', 'section_name')->orderBy('section_name')->get();
        $allClasses = SchoolClass::select('id', 'name')->orderBy('name')->get();
        $terms = Term::selectRaw('MIN(id) as id, name')
            ->groupBy('name')
            ->orderBy('name')
            ->get();
        $sessions = Session::select('name')->distinct()->orderBy('name')->get();

        if ($request->filled('filter_section')) {
            $classes = SchoolClass::where('section_id', $request->filter_section)
                ->select('id', 'name')
                ->orderBy('name')
                ->get();
        } else {
            $classes = $allClasses;
        }

        return view('manage_fee_prospectus', compact('prospectuses', 'sections', 'classes', 'allClasses', 'terms', 'sessions'));
    }

    public function previewFeeProspectus($id)
    {
        try {
            $decryptedId = Crypt::decrypt($id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404);
        }

        $prospectus = FeeProspectus::with(['section', 'schoolClass', 'term.session'])->findOrFail($decryptedId);

        $pdf = Pdf::loadView('fee_prospectus', compact('prospectus'));

        return $pdf->stream('fee-prospectus-' . $prospectus->schoolClass->name . '-' . $prospectus->term->name . '.pdf');
    }

    public function editFeeProspectus($id)
    {
        try {
            $decryptedId = Crypt::decrypt($id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404);
        }
        $prospectus = FeeProspectus::with(['section', 'schoolClass', 'term'])->findOrFail($decryptedId);
        $section = $prospectus->section;
        $class = $prospectus->schoolClass;
        $term = $prospectus->term;
        $fees = $prospectus->items ?? [];

        return view('edit_fee_prospectus_details', compact('prospectus', 'section', 'class', 'term', 'fees'));
    }

    public function updateFeeProspectus(Request $request, $id)
    {
        try {
            $decryptedId = Crypt::decrypt($id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404);
        }
        $prospectus = FeeProspectus::findOrFail($decryptedId);

        $request->validate([
            'fees' => 'required|array|min:1',
            'fees.*.item' => 'required|string|max:255',
            'fees.*.amount' => 'required|numeric|min:0',
        ]);

        // Prepare items array and calculate total
        $items = [];
        $totalAmount = 0;
        foreach ($request->fees as $fee) {
            $items[] = [
                'item' => $fee['item'],
                'amount' => $fee['amount'],
            ];
            $totalAmount += $fee['amount'];
        }

        // Update prospectus
        $prospectus->update([
            'items' => $items,
            'total_amount' => $totalAmount,
        ]);

        return redirect()->route('fee.prospectus.manage')->with('success', 'Fee Prospectus updated successfully.');
    }

    public function destroyFeeProspectus($id)
    {
        try {
            $decryptedId = Crypt::decrypt($id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404);
        }
        $prospectus = FeeProspectus::findOrFail($decryptedId);
        $prospectus->delete();

        return redirect()->route('fee.prospectus.manage')->with('success', 'Fee Prospectus deleted successfully.');
    }

    public function printReceipt(Payment $payment)
    {
        $student = User::findOrFail($payment->student_id);
        $section = Section::findOrFail($payment->section_id);
        $class = SchoolClass::findOrFail($payment->class_id);
        $currentTerm = Term::findOrFail($payment->term_id);
        $session = Session::findOrFail($payment->session_id);

        $prospectus = FeeProspectus::where('section_id', $payment->section_id)
            ->where('class_id', $payment->class_id)
            ->where('term_id', $payment->term_id)
            ->first();

        $totalDue = $prospectus ? $prospectus->total_amount : 0;

        $allPaymentsSum = Payment::where('student_id', $payment->student_id)
            ->where('term_id', $payment->term_id)
            ->where('session_id', $payment->session_id)
            ->sum('amount');

        $balance = $totalDue - $allPaymentsSum;

        $pdf = Pdf::loadView('payment_receipt', compact(
            'payment',
            'student',
            'section',
            'class',
            'currentTerm',
            'session',
            'totalDue',
            'balance'
        ));

        // Set paper size suitable for thermal printer (approx 80mm width, custom height)
        $pdf->setPaper([0, 0, 227, 500], 'mm'); // 80mm width ≈ 227 points, height adjusted

        $filename = 'payment-receipt-' . $student->admission_no . '-' . $payment->id . '.pdf';

        return $pdf->stream($filename);
    }
}
