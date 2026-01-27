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
                            <form action="{{ route('settings.school.update') }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                                @csrf
                                @method('PUT')

                                <div class="card-header">
                                    <h4>School Settings</h4>
                                </div>

                                <div class="card-body">
                                    @if(session('success'))
                                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                                            {{ session('success') }}
                                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                                        </div>
                                    @endif

                                    <div class="form-group col-md-8 px-0">
                                        <label>School Name <span class="text-danger">*</span></label>
                                        <input type="text" name="school_name" class="form-control" required
                                               value="{{ old('school_name', $settings->school_name) }}"
                                               placeholder="e.g. St. Mary's International School">
                                        <div class="invalid-feedback">
                                            Please enter the school name.
                                        </div>
                                    </div>

                                    <div class="form-group col-md-8 px-0">
                                        <label>School Address</label>
                                        <textarea name="address" class="form-control" rows="4"
                                                  placeholder="Enter full school address">{{ old('address', $settings->address) }}</textarea>
                                    </div>

                                    <div class="form-group col-md-8 px-0">
                                        <label>School Logo <small class="text-muted">(Max 150KB, PNG/JPG/GIF/WEBP)</small></label>
                                        <div class="custom-file">
                                            <input type="file" name="logo" class="custom-file-input" id="logo" accept="image/*">
                                            <label class="custom-file-label" for="logo">Choose file</label>
                                        </div>
                                        <small class="form-text text-muted">Recommended size: 200x200px or similar square ratio.</small>

                                        @if($settings->logo)
                                            <div class="mt-3">
                                                <p><strong>Current Logo:</strong></p>
                                                <img src="{{ asset('storage/logos/' . $settings->logo) }}"
                                                     alt="Current School Logo" style="max-height: 150px; border: 1px solid #ddd; border-radius: 8px;">
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="card-footer text-left pt-5 mt-3">
                                    <button class="btn btn-primary" type="submit">Save Settings</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        @include('includes.footer')
    </div>

    <script>
        // Update file input label with selected filename
        document.querySelector('.custom-file-input').addEventListener('change', function(e) {
            var fileName = e.target.files[0].name;
            var label = e.target.nextElementSibling;
            label.innerText = fileName;
        });

        // Form validation
        (function() {
            'use strict';
            window.addEventListener('load', function() {
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
            }, false);
        })();
    </script>
</body>
</html>