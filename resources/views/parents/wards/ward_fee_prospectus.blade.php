@include('includes.head')

<body>
    <div class="loader"></div>
    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            <div class="navbar-bg"></div>
            @include('includes.right_top_nav')
            @include('includes.side_nav')

            <div class="main-content pt-5 mt-5">
                <section class="section mb-5 pb-1 px-0">
                    <!-- Header Card with School Info -->
                    <div class="card mx-1 border-0 shadow-sm">
                        <div class="card-body py-3">
                            <div class="d-flex align-items-center">
                                <img src="{{ school_logo() }}" alt="{{ school_name() ?? 'School Logo' }}"
                                    style="width: 50px; height: 50px; object-fit: contain;" class="mr-3">
                                <div>
                                    <h5 class="mb-0 font-weight-bold">{{ school_name() ?? 'School Management System' }}
                                    </h5>
                                    <small class="text-muted">Fee Prospectus</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <!-- Student Information Header -->
                            <div class="card-header bg-white border-bottom">
                                <div class="row align-items-start">
                                    <div class="col-md-6">
                                        <h4 class="mb-2 font-weight-bold text-dark">{{ $student->name }}</h4>
                                        <div class="mb-1 text-dark">
                                            <i class="fas fa-id-card mr-1"></i>
                                            Admission No: <strong>{{ $student->admission_no ?? 'N/A' }}</strong>
                                        </div>
                                    </div>

                                    <div class="col-md-6 text-md-right mt-3 mt-md-0">
                                        <div class="d-flex justify-content-between flex-wrap gap-3">
                                            <div class="text-dark">
                                                <small class="">Class:</small>
                                                <strong class="ml-1">{{ $student->schoolClass->name ?? 'Not Assigned'
                                                    }}</strong>
                                            </div>

                                            <div class="text-dark">
                                                <small class="">Term:</small>
                                                <strong class="ml-1">{{ $currentTerm->name }}</strong>
                                            </div>

                                            <div class="text-dark">
                                                <small class="">Session:</small>
                                                <strong class="ml-1">{{ $currentTerm->session->name ?? 'N/A' }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="card-body py-4">
                                @if($prospectus)
                                <div class="mb-4">
                                    <div class="d-flex align-items-center mb-4">
                                        <div class="bg-light rounded p-2 mr-3">
                                            <i class="fas fa-file-invoice-dollar fa-2x text-primary"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-0 font-weight-bold">Fee Breakdown</h5>
                                            <small class="text-muted">{{ $currentTerm->name }} - {{
                                                $currentTerm->session->name ?? 'N/A' }}</small>
                                        </div>
                                    </div>

                                    <div class="table-responsive col-md-7">
                                        <table class="table table-hover table-sm mb-0"
                                            style="border: 1px solid #e3e6f0;">
                                            <thead style="background-color: #f8f9fc;">
                                                <tr>
                                                    <th width="8%" class="text-center border-0 py-3">#</th>
                                                    <th width="62%" class="border-0 py-3">Fee Item</th>
                                                    <th width="30%" class="text-right border-0 py-3">Amount (₦)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($prospectus->items as $index => $item)
                                                <tr style="border-bottom: 1px solid #e3e6f0;">
                                                    <td class="text-center py-3">
                                                        <span class="badge badge-light">{{ $index + 1 }}</span>
                                                    </td>
                                                    <td class="py-3">
                                                        <span class="font-weight-500">{{ $item['item'] }}</span>
                                                    </td>
                                                    <td class="text-right font-weight-bold py-3">
                                                        {{ number_format($item['amount'], 2) }}
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="2" class="font-weight-bold py-3 border-0"
                                                        style="font-size: 1.1rem;">
                                                        Total Amount Due
                                                    </td>
                                                    <td class="text-right font-weight-bold py-3 border-0"
                                                        style="font-size: 1rem;">
                                                        ₦{{ number_format($prospectus->total_amount, 2) }}
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>

                                @if($prospectus->notes)
                                <div class="alert border-left-primary shadow-sm mt-4"
                                    style="border-left: 4px solid #4e73df; background-color: #f8f9fc;">
                                    <div class="d-flex align-items-start">
                                        <i class="fas fa-info-circle text-primary mr-3 mt-1"></i>
                                        <div>
                                            <strong class="d-block mb-1">Important Note:</strong>
                                            <span class="text-muted">{{ $prospectus->notes }}</span>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <div
                                    class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center flex-wrap">
                                    <a href="{{ route('wards.index') }}" class="btn btn-secondary mb-2 px-4 btn-sm">
                                        <i class="fas fa-arrow-left mr-2"></i> Back to My Wards
                                    </a>
                                    <div class="mb-2">
                                        <button onclick="window.print()"
                                            class="btn btn-outline-primary mr-2 px-4 btn-sm">
                                            <i class="fas fa-print mr-2"></i> Print
                                        </button>
                                        {{-- <a href="{{ route('ward.fee.payment', $student->id) }}"
                                            class="btn btn-primary px-4">
                                            <i class="fas fa-credit-card mr-2"></i> Proceed to Payment
                                        </a> --}}
                                    </div>
                                </div>
                                @else
                                <!-- No Prospectus Available -->
                                <div class="text-center py-5">
                                    <div class="mb-4">
                                        <div class="d-inline-block p-4 rounded-circle"
                                            style="background-color: #f8f9fc;">
                                            <i class="fas fa-info-circle fa-4x text-muted"></i>
                                        </div>
                                    </div>
                                    <h4 class="mb-3 font-weight-bold">No Fee Prospectus Available</h4>
                                    <p class="text-muted mb-2" style="max-width: 500px; margin: 0 auto;">
                                        No fee prospectus has been set for
                                        <strong>{{ $student->schoolClass->name ?? 'this class' }}</strong>
                                        in the current term.
                                    </p>
                                    <p class="text-muted mb-4">
                                        <small><strong>{{ $currentTerm->name }}</strong> - {{
                                            $currentTerm->session->name ?? 'N/A' }}</small>
                                    </p>
                                    <div class="alert border-left-warning shadow-sm d-inline-block text-left mt-3"
                                        style="border-left: 4px solid #f6c23e; background-color: #fff3cd; max-width: 500px;">
                                        <small class="text-dark">
                                            <i class="fas fa-exclamation-triangle mr-2"></i>
                                            Please contact the school administration for more information.
                                        </small>
                                    </div>
                                    <div class="mt-4">
                                        <a href="{{ route('wards.index') }}" class="btn btn-secondary px-4">
                                            <i class="fas fa-arrow-left mr-2"></i> Back to My Wards
                                        </a>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    @include('includes.edit_footer')

    <style>
        .font-weight-500 {
            font-weight: 500;
        }

        /* .border-left-primary {
            border-left: 4px solid #4e73df !important;
        } */

        /* .border-left-warning {
            border-left: 4px solid #f6c23e !important;
        } */

        .shadow-sm {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
        }

        .table-hover tbody tr:hover {
            background-color: #f8f9fc;
        }

        @media print {

            .navbar-bg,
            .main-sidebar,
            .section-header-breadcrumb,
            .btn,
            .loader {
                display: none !important;
            }

            .main-content {
                padding: 0 !important;
                margin: 0 !important;
            }

            .card {
                border: 1px solid #dee2e6 !important;
                box-shadow: none !important;
            }

            .card-header {
                background-color: #f8f9fc !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            thead {
                background-color: #f8f9fc !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            tfoot {
                background-color: #f8f9fc !important;
                /* border-top: 2px solid #4e73df !important; */
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .alert {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            body {
                padding: 20px;
            }
        }

        @media (max-width: 768px) {
            .table-responsive {
                border: 1px solid #e3e6f0;
                border-radius: 4px;
            }
        }
    </style>
</body>