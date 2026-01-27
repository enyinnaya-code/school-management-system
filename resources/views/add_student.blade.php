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
                            <div class="card-header">
                                <h4>Add New Student</h4>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('students.store') }}" method="POST">
                                    @csrf

                                    <!-- STUDENT INFORMATION -->
                                    <p class="mb-0 font-weight-bold">Student Information</p>
                                    <hr>
                                    <div class="form-group row mb-4">
                                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Student Name</label>
                                        <div class="col-sm-12 col-md-7">
                                            <input type="text" name="student_name" id="student_name" class="form-control" required
                                                oninput="this.value = this.value.toUpperCase(); generateEmail();">
                                        </div>
                                    </div>

                                    <div class="form-group row mb-4">
                                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Date of Birth</label>
                                        <div class="col-sm-12 col-md-7">
                                            <input type="date" name="dob" class="form-control" required>
                                        </div>
                                    </div>

                                    <div class="form-group row mb-4">
                                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Gender</label>
                                        <div class="col-sm-12 col-md-7">
                                            <select name="gender" class="form-control" required>
                                                <option value="">Select Gender</option>
                                                <option value="Male">Male</option>
                                                <option value="Female">Female</option>
                                            </select>
                                        </div>
                                    </div>

                                   

                                    <!-- CONTACT DETAILS -->
                                    <p class="mb-0 font-weight-bold">Contact Details</p>
                                    <hr>
                                    <div class="form-group row mb-4">
                                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Address</label>
                                        <div class="col-sm-12 col-md-7">
                                            <textarea name="address" class="form-control" rows="2"></textarea>
                                        </div>
                                    </div>

                                    <div class="form-group row mb-4">
                                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Phone</label>
                                        <div class="col-sm-12 col-md-7">
                                            <input type="tel" name="phone" class="form-control">
                                        </div>
                                    </div>

                                    <div class="form-group row mb-4">
                                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Email</label>
                                        <div class="col-sm-12 col-md-7">
                                            <div class="input-group">
                                                <input type="email" name="email" id="email" class="form-control">
                                                <div class="input-group-append">
                                                    <span class="input-group-text">@sms.com</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- GUARDIAN INFORMATION -->
                                    <p class="mb-0 font-weight-bold">Parent/Guardian Details</p>
                                    <hr>
                                    <div class="form-group row mb-4">
                                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Full Name</label>
                                        <div class="col-sm-12 col-md-7">
                                            <input type="text" name="guardian_name" class="form-control">
                                        </div>
                                    </div>

                                    <div class="form-group row mb-4">
                                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Phone</label>
                                        <div class="col-sm-12 col-md-7">
                                            <input type="tel" name="guardian_phone" class="form-control">
                                        </div>
                                    </div>

                                    <div class="form-group row mb-4">
                                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Email</label>
                                        <div class="col-sm-12 col-md-7">
                                            <input type="email" name="guardian_email" class="form-control">
                                        </div>
                                    </div>

                                    <div class="form-group row mb-4">
                                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Contact Address</label>
                                        <div class="col-sm-12 col-md-7">
                                            <textarea name="guardian_address" class="form-control" rows="2"></textarea>
                                        </div>
                                    </div>

                                    <!-- ACADEMIC INFORMATION -->
                                    <p class="mb-0 font-weight-bold">Academic Info</p>
                                    <hr>
                                    <div class="form-group row mb-4">
                                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Section</label>
                                        <div class="col-sm-12 col-md-7">
                                            <select name="section_id" id="section_id" class="form-control" required>
                                                <option value="">-- Select Section --</option>
                                                @foreach($sections as $section)
                                                <option value="{{ $section->id }}">{{ $section->section_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group row mb-4">
                                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Class</label>
                                        <div class="col-sm-12 col-md-7">
                                            <select name="class_id" id="class_id" class="form-control" required>
                                                <option value="">-- Select Class --</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="card-footer text-left mt-5 pt-5">
                                        <button type="submit" class="btn btn-primary">Add Student</button>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>


    <script>
        // Function to generate email from student name
        function generateEmail() {
            let studentName = document.getElementById('student_name').value;
            let emailField = document.getElementById('email');

            // Remove spaces and special characters, convert to lowercase
            let cleanName = studentName.replace(/[^a-zA-Z0-9]/g, '').toLowerCase();

            // Set the email field value (without @sms.com as it's shown in the append)
            emailField.value = cleanName + '@sms.com';
        }

        document.getElementById('section_id').addEventListener('change', function() {
            let sectionId = this.value;
            let classSelect = document.getElementById('class_id');
            classSelect.innerHTML = '<option value="">-- Select Class --</option>'; // Reset classes

            if (sectionId) {
                fetch(`/get-classes/${sectionId}`)
                    .then(res => res.json())
                    .then(data => {
                        data.forEach(cls => {
                            let option = document.createElement('option');
                            option.value = cls.id;
                            option.textContent = cls.name;
                            classSelect.appendChild(option);
                        });
                    })
                    .catch(err => console.error('Error fetching classes:', err));
            }
        });

        // Add this to prevent double form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Adding...';
        });
    </script>

    @include('includes.edit_footer')