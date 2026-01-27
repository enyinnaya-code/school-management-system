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
                            <form action="{{ route('eclass.store') }}" method="POST" class="needs-validation" novalidate>
                                @csrf

                                <div class="card-header">
                                    <h4>Create E-Classroom Session</h4>
                                </div>

                                <div class="card-body">
                                    <div class="row">
                                        <div class="form-group col-md-8">
                                            <label>Title <span class="text-danger">*</span></label>
                                            <input type="text" name="title" class="form-control" required placeholder="e.g. Mathematics Lesson - Algebra">
                                        </div>

                                        <div class="form-group col-md-4">
                                            <label>Duration (minutes) <span class="text-danger">*</span></label>
                                            <input type="number" name="duration_minutes" class="form-control" value="60" min="15" max="300" required>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Description</label>
                                        <textarea name="description" class="form-control" rows="3" placeholder="Optional session details..."></textarea>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label>Class (Optional)</label>
                                            <select name="class_id" class="form-control">
                                                <option value="">All Classes / General</option>
                                                @foreach(\App\Models\SchoolClass::all() as $class)
                                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label>Subject (Optional)</label>
                                            <select name="course_id" class="form-control">
                                                <option value="">None</option>
                                                @foreach(\App\Models\Course::all() as $course)
                                                    <option value="{{ $course->id }}">{{ $course->course_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label>Start Time <span class="text-danger">*</span></label>
                                            <input type="datetime-local" name="start_time" class="form-control" required>
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label>Room Password (Optional)</label>
                                            <input type="text" name="password" class="form-control" placeholder="Leave blank for open access">
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer text-left pt-5 mt-3">
                                    <button class="btn btn-primary" type="submit">Create Session</button>
                                    <a href="{{ route('eclass.index') }}" class="btn btn-secondary ml-2">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        @include('includes.edit_footer')
    </div>
</body>

<script>
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