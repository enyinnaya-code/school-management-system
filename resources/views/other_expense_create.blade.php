{{-- resources/views/other_expense_create.blade.php --}}
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
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Create Other Expense</h4>
                            </div>
                            <div class="card-body">

                                @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show">
                                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                                </div>
                                @endif

                                @if(session('error'))
                                <div class="alert alert-danger alert-dismissible fade show">
                                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                                </div>
                                @endif

                                <form action="{{ route('other.expense.store') }}" method="POST" class="px-2">
                                    @csrf
                                    <div class="form-card">

                                        {{-- Row 1: Session + Term --}}
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Session <span class="text-danger">*</span></label>
                                                    <select name="session_id" id="session_id"
                                                        class="form-control @error('session_id') is-invalid @enderror"
                                                        required>
                                                        <option value="">-- Select Session --</option>
                                                        @foreach($sessions as $session)
                                                            <option value="{{ $session->id }}"
                                                                {{ old('session_id', $currentSession?->id) == $session->id ? 'selected' : '' }}>
                                                                {{ $session->name }}
                                                                @if($session->is_current) (Current) @endif
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('session_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Term <span class="text-danger">*</span></label>
                                                    <select name="term_id" id="term_id"
                                                        class="form-control @error('term_id') is-invalid @enderror"
                                                        required>
                                                        <option value="">-- Select Term --</option>
                                                        @foreach($terms as $term)
                                                            <option value="{{ $term->id }}"
                                                                {{ old('term_id', $currentTerm?->id) == $term->id ? 'selected' : '' }}>
                                                                {{ $term->name }}
                                                                @if($term->is_current) (Current) @endif
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('term_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Row 2: Section --}}
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Section <small class="text-muted">(optional — leave blank for school-wide)</small></label>
                                                    <select name="section_id"
                                                        class="form-control @error('section_id') is-invalid @enderror">
                                                        <option value="">All Sections</option>
                                                        @foreach($sections as $section)
                                                            <option value="{{ $section->id }}"
                                                                {{ old('section_id') == $section->id ? 'selected' : '' }}>
                                                                {{ $section->section_name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('section_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- Row 3: Amount --}}
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Amount (₦) <span class="text-danger">*</span></label>
                                                    <input type="number" step="0.01" name="amount"
                                                        class="form-control @error('amount') is-invalid @enderror"
                                                        value="{{ old('amount') }}" required min="0">
                                                    @error('amount')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Row 4: Description --}}
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>Description <span class="text-danger">*</span></label>
                                                    <textarea name="description"
                                                        class="form-control @error('description') is-invalid @enderror"
                                                        rows="3" required>{{ old('description') }}</textarea>
                                                    @error('description')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                    </div>{{-- /form-card --}}

                                    <div class="row">
                                        <div class="col-12 text-right">
                                            <a href="{{ route('other.expense.manage') }}" class="btn btn-secondary">
                                                <i class="fas fa-arrow-left"></i> Back
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save"></i> Save Expense
                                            </button>
                                        </div>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        @include('includes.edit_footer')
    </div>

    <script>
        $(document).ready(function () {

            setTimeout(function () { $('.alert').fadeOut('slow'); }, 5000);

            // Load terms when session changes
            $('#session_id').on('change', function () {
                const sessionId = $(this).val();
                const termSelect = $('#term_id');

                termSelect.html('<option value="">-- Loading... --</option>');

                if (!sessionId) {
                    termSelect.html('<option value="">-- Select Term --</option>');
                    return;
                }

                $.get('/other-expense/terms/' + sessionId, function (terms) {
                    let options = '<option value="">-- Select Term --</option>';
                    terms.forEach(function (term) {
                        const label = term.is_current ? term.name + ' (Current)' : term.name;
                        const selected = term.is_current ? 'selected' : '';
                        options += `<option value="${term.id}" ${selected}>${label}</option>`;
                    });
                    termSelect.html(options);
                });
            });

        });
    </script>
</body>