<?php

namespace App\Http\Controllers;

use App\Models\MiscFee;
use App\Models\MiscFeePayment;
use App\Models\User;
use App\Models\Section;
use App\Models\Session;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class MiscFeePaymentController extends Controller
{
    /**
     * Display a listing of misc fee payments.
     */
    public function index(Request $request)
    {
        $query = MiscFeePayment::with(['miscFeeType', 'student', 'paidBy'])
            ->orderBy('payment_date', 'desc')
            ->orderBy('created_at', 'desc');

        if ($request->filled('filter_student')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->filter_student . '%')
                  ->orWhere('admission_no', 'like', '%' . $request->filter_student . '%');
            });
        }

        if ($request->filled('filter_fee_type')) {
            $query->where('misc_fee_type_id', $request->filter_fee_type);
        }

        if ($request->filled('filter_date_from')) {
            $query->whereDate('payment_date', '>=', $request->filter_date_from);
        }

        if ($request->filled('filter_date_to')) {
            $query->whereDate('payment_date', '<=', $request->filter_date_to);
        }

        if ($request->filled('filter_status')) {
            $query->where('status', $request->filter_status);
        }

        $payments  = $query->paginate(10);
        $students  = User::where('user_type', 4)->orderBy('name')->get();
        $feeTypes  = MiscFee::orderBy('name')->get();

        return view('misc_fee_payments_manage', compact('payments', 'students', 'feeTypes'));
    }

    /**
     * Show the form for creating a new payment.
     */
    public function create()
    {
        $sections = Section::orderBy('section_name')->get();
        $feeTypes = MiscFee::orderBy('name')->get();

        return view('misc_fee_payment_create', compact('sections', 'feeTypes'));
    }

    /**
     * Fetch all school-wide sessions via AJAX.
     * $section_id param kept for route compatibility but ignored.
     */
    public function getSessions($section_id)
    {
        $sessions = Session::orderByDesc('name')
            ->select('id', 'name', 'is_current')
            ->get();

        return response()->json($sessions);
    }

    /**
     * Fetch classes for a section via AJAX.
     */
    public function getClasses($section_id)
    {
        $classes = SchoolClass::where('section_id', $section_id)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json($classes);
    }

    /**
     * Fetch students for a class via AJAX.
     */
    public function getStudents($class_id)
    {
        $students = User::where('user_type', 4)
            ->where('class_id', $class_id)
            ->select('id', 'name', 'admission_no')
            ->orderBy('name')
            ->get();

        return response()->json($students);
    }

    /**
     * Store a newly created payment.
     */
    public function store(Request $request)
    {
        $request->validate([
            'section_id'       => 'required|exists:sections,id',
            'session_id'       => 'required|exists:school_sessions,id',
            'class_id'         => 'required|exists:school_classes,id',
            'student_id'       => 'required|exists:users,id',
            'misc_fee_type_id' => 'required|exists:misc_fee_types,id',
            'amount_paid'      => 'required|numeric|min:0',
            'payment_date'     => 'required|date',
            'status'           => 'nullable|in:pending,paid,cancelled',
        ]);

        // Generate unique receipt number
        do {
            $receiptNumber = 'MFP-' . Str::upper(Str::random(8)) . '-' . Carbon::now()->format('Ymd');
        } while (MiscFeePayment::where('receipt_number', $receiptNumber)->exists());

        DB::beginTransaction();
        try {
            $payment = MiscFeePayment::create([
                'misc_fee_type_id' => $request->misc_fee_type_id,
                'student_id'       => $request->student_id,
                'amount_paid'      => $request->amount_paid,
                'payment_date'     => $request->payment_date,
                'receipt_number'   => $receiptNumber,
                'status'           => $request->status ?? 'paid',
                'paid_by'          => Auth::id(),
            ]);

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success'     => true,
                    'message'     => 'Miscellaneous fee payment recorded successfully!',
                    'receipt_url' => route('misc.fee.payments.receipt', $payment->id),
                ]);
            }

            return redirect()
                ->route('misc.fee.payments.manage')
                ->with('success', 'Miscellaneous fee payment recorded successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error recording payment: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'Error recording payment: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display receipt in browser for direct printing on 80mm Xprinter.
     */
    public function receipt($id)
    {
        $payment = MiscFeePayment::with(['miscFeeType', 'student', 'paidBy'])->findOrFail($id);

        $student = $payment->student;
        $class   = SchoolClass::find($student->class_id);
        $section = $class ? Section::find($class->section_id) : null;

        // School-wide current session & term
        $session     = Session::where('is_current', true)->first();
        $currentTerm = $session
            ? \App\Models\Term::where('session_id', $session->id)->where('is_current', true)->first()
            : null;

        // Total due from the fee type; balance = due - amount already paid
        $totalDue = $payment->miscFeeType->amount ?? $payment->amount_paid;
        $balance  = max(0, $totalDue - $payment->amount_paid);

        return view('misc_fee_receipt', compact(
            'payment', 'student', 'class', 'section',
            'session', 'currentTerm', 'totalDue', 'balance'
        ));
    }
}