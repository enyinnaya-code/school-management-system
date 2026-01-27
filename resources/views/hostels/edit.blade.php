<!-- resources/views/hostels/edit.blade.php -->

@include('includes.head')

<body>
    <div class="loader"></div>
    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            <div class="navbar-bg"></div>
            @include('includes.right_top_nav')
            @include('includes.side_nav')
            <!-- Main Content -->
            <div class="main-content pt-5 mt-5">
                <section class="section mb-5 pb-1 px-0">
                    <div class="col-12">
                        <div class="card">
                            <form action="{{ route('hostels.update', $hostel->id) }}" method="POST" class="needs-validation" novalidate>
                                @csrf
                                @method('PUT')

                                <div class="card-header">
                                    <h4>Edit Hostel</h4>
                                </div>
                                <div class="card-body">
                                    <div class="form-group col-md-6 px-0">
                                        <label>Name</label>
                                        <input type="text" name="name" class="form-control" required placeholder="e.g. Blue Hostel" value="{{ $hostel->name }}">
                                        <div class="valid-feedback">
                                            Looks good!
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6 px-0">
                                        <label>Description</label>
                                        <textarea name="description" class="form-control" placeholder="Brief description of the hostel">{{ $hostel->description }}</textarea>
                                    </div>

                                    <div class="form-group col-md-6 px-0">
                                        <label>Teachers in Charge</label>
                                        <select name="warden_ids[]" id="warden_select" class="form-control" multiple style="width: 100%;">
                                            @foreach($teachers as $teacher)
                                                <option value="{{ $teacher->id }}" {{ $hostel->wardens->contains($teacher->id) ? 'selected' : '' }}>{{ $teacher->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="card-footer text-left pt-5 mt-3">
                                    <button class="btn btn-primary" type="submit">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        @include('includes.edit_footer')
    </div>

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <!-- jQuery (required for Select2) -->
    {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}
    
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Validation & Select2 Initialization Script -->
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('#warden_select').select2({
                placeholder: 'Select teachers in charge',
                allowClear: true,
                width: '100%'
            });

            // Form validation
            (function() {
                'use strict';
                const forms = document.getElementsByClassName('needs-validation');
                Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (!form.checkValidity()) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            })();
        });
    </script>
</body>