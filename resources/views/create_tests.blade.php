{{-- @include('includes.head')

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
                                <h4>Create New Class Test</h4>
                            </div>

                            <div class="card-body">
                                <!-- Assignment Form -->
                                <form method="POST" action="">
                                    @csrf

                                    <!-- Assignment Title -->
                                    <div class="form-group row px-3">
                                        <label class="col-md-2 col-form-label">Title</label>
                                        <div class="col-md-6">
                                            <input type="text" name="title" class="form-control" placeholder="Enter assignment title" required>
                                            @error('title')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Assignment Description -->
                                    <div class="form-group row px-3">
                                        <label class="col-md-2 col-form-label">Description</label>
                                        <div class="col-md-6">
                                            <textarea name="description" class="form-control" rows="4" placeholder="Enter assignment description"></textarea>
                                            @error('description')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Due Date -->
                                    <div class="form-group row px-3">
                                        <label class="col-md-2 col-form-label">Due Date</label>
                                        <div class="col-md-4">
                                            <input type="date" name="due_date" class="form-control" required>
                                            @error('due_date')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Total Marks -->
                                    <div class="form-group row px-3">
                                        <label class="col-md-2 col-form-label">Total Marks</label>
                                        <div class="col-md-4">
                                            <input type="number" name="total_marks" class="form-control" min="1" placeholder="Enter total marks" required>
                                            @error('total_marks')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Class Selection (Hard-coded for now) -->
                                    <div class="form-group row px-3">
                                        <label class="col-md-2 col-form-label">Class</label>
                                        <div class="col-md-4">
                                            <select class="form-control" name="class_id" required>
                                                <option value="">Select class...</option>
                                                <option value="1">JSS 1</option>
                                                <option value="2">JSS 2</option>
                                                <option value="3">SSS 1</option>
                                            </select>
                                            @error('class_id')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="form-group mt-4 pt-4">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-plus-circle"></i> Create Test
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    @include('includes.edit_footer')
</body> --}}
