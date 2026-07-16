@include('includes.head')

<body>
    <div class="loader"></div>
    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            <div class="navbar-bg"></div>
            @include('includes.right_top_nav')
            @include('includes.side_nav')

            <div class="main-content pt-5 mt-5">
                <section class="section">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>Report Card - {{ strtoupper($student->name) }}</h4>
                                <a href="{{ route('students.reportcards.print') }}" target="_blank"
                                    class="btn btn-success btn-lg">
                                    <i class="fas fa-print"></i> Print Report Card
                                </a>
                            </div>
                            <div class="card-body p-4">
                                @include('students.report_cards._report_card_body')
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        @include('includes.edit_footer')
    </div>
</body>