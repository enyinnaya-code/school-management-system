<!-- resources/views/e_library/edit_resource.blade.php -->

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
                    <div class="col-12 col-lg-10 mx-auto">
                        <div class="card shadow-sm">
                            <form action="{{ route('e_library.update_resource', $resource->id) }}" 
                                  method="POST" 
                                  class="needs-validation" 
                                  novalidate 
                                  enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <div class="card-header bg-warning text-dark">
                                    <h4 class="mb-0">
                                        <i class="fas fa-edit mr-2"></i> Edit Resource
                                    </h4>
                                </div>

                                <div class="card-body">
                                    <!-- Success / Error Messages -->
                                    @if(session('success'))
                                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                                            {{ session('success') }}
                                            <button type="button" class="close" data-dismiss="alert">×</button>
                                        </div>
                                    @endif

                                    @if($errors->any())
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <ul class="mb-0 pl-3">
                                                @foreach($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                            <button type="button" class="close" data-dismiss="alert">×</button>
                                        </div>
                                    @endif

                                    <div class="row">
                                        <!-- Title -->
                                        <div class="col-md-6 mb-3">
                                            <label for="title">Title <span class="text-danger">*</span></label>
                                            <input type="text" name="title" id="title" class="form-control" required 
                                                   placeholder="e.g. Introduction to Computer Science" 
                                                   value="{{ old('title', $resource->title) }}">
                                            <div class="invalid-feedback">Please enter a title.</div>
                                        </div>

                                        <!-- Author -->
                                        <div class="col-md-6 mb-3">
                                            <label for="author">Author <span class="text-danger">*</span></label>
                                            <input type="text" name="author" id="author" class="form-control" required 
                                                   placeholder="e.g. John Doe" 
                                                   value="{{ old('author', $resource->author) }}">
                                            <div class="invalid-feedback">Please enter the author name.</div>
                                        </div>

                                        <!-- Description -->
                                        <div class="col-md-12 mb-3">
                                            <label for="description">Description</label>
                                            <textarea name="description" id="description" class="form-control" rows="3" 
                                                      placeholder="Brief description of the resource">{{ old('description', $resource->description) }}</textarea>
                                        </div>

                                        <!-- Resource Type -->
                                        <div class="col-md-6 mb-3">
                                            <label for="resource_type">Resource Type <span class="text-danger">*</span></label>
                                            <select name="resource_type" id="resource_type" class="form-control" required>
                                                <option value="">-- Select Type --</option>
                                                <option value="pdf" {{ old('resource_type', $resource->resource_type) == 'pdf' ? 'selected' : '' }}>PDF</option>
                                                <option value="docx" {{ old('resource_type', $resource->resource_type) == 'docx' ? 'selected' : '' }}>Word Document (DOCX)</option>
                                                <option value="xlsx" {{ old('resource_type', $resource->resource_type) == 'xlsx' ? 'selected' : '' }}>Excel Spreadsheet (XLSX)</option>
                                                <option value="pptx" {{ old('resource_type', $resource->resource_type) == 'pptx' ? 'selected' : '' }}>PowerPoint (PPTX)</option>
                                                <option value="ebook" {{ old('resource_type', $resource->resource_type) == 'ebook' ? 'selected' : '' }}>Ebook (EPUB)</option>
                                                <option value="link" {{ old('resource_type', $resource->resource_type) == 'link' ? 'selected' : '' }}>External URL/Link</option>
                                            </select>
                                            <div class="invalid-feedback">Please select a resource type.</div>
                                        </div>

                                        <!-- Publisher -->
                                        <div class="col-md-6 mb-3">
                                            <label for="publisher">Publisher</label>
                                            <input type="text" name="publisher" id="publisher" class="form-control" 
                                                   placeholder="e.g. Pearson Education" 
                                                   value="{{ old('publisher', $resource->publisher) }}">
                                        </div>

                                        <!-- Publication Year -->
                                        <div class="col-md-6 mb-3">
                                            <label for="publication_year">Publication Year</label>
                                            <input type="number" name="publication_year" id="publication_year" class="form-control" 
                                                   min="1800" max="{{ date('Y') }}" placeholder="e.g. 2023" 
                                                   value="{{ old('publication_year', $resource->publication_year) }}">
                                            <div class="invalid-feedback">Please enter a valid year (1800–{{ date('Y') }}).</div>
                                        </div>

                                        <!-- Current File Display & New File Upload -->
                                        <div class="col-md-6 mb-3" id="file_upload">
                                            <label for="file">Replace File (Max 5MB)</label>
                                            @if($resource->file_path)
                                                <div class="mb-2">
                                                    <small class="text-muted">
                                                        <strong>Current file:</strong> 
                                                        <a href="{{ route('e_library.view_resource', $resource->id) }}" target="_blank">
                                                            {{ basename($resource->file_path) }}
                                                        </a>
                                                    </small>
                                                </div>
                                            @endif
                                            <input type="file" name="file" id="file" class="form-control" 
                                                   accept=".pdf,.docx,.xlsx,.pptx,.epub">
                                            <small class="text-muted">Supported: PDF, DOCX, XLSX, PPTX, EPUB (Leave blank to keep current file)</small>
                                            <div class="invalid-feedback">Please upload a valid file (max 5MB).</div>
                                        </div>

                                        <!-- URL Input (for link type) -->
                                        <div class="col-md-6 mb-3" id="url_input">
                                            <label for="url">External URL <span class="text-danger">*</span></label>
                                            <input type="url" name="url" id="url" class="form-control" 
                                                   placeholder="https://example.com/resource.pdf" 
                                                   value="{{ old('url', $resource->url) }}">
                                            <div class="invalid-feedback">Please enter a valid URL.</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer text-right">
                                    <a href="{{ route('e_library.manage_resources') }}" class="btn btn-secondary mr-2">Cancel</a>
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-save mr-1"></i> Update Resource
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        @include('includes.edit_footer')
    </div>

    <!-- JavaScript: Dynamic Field Visibility + Required Handling -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const resourceType = document.getElementById('resource_type');
            const fileUploadDiv = document.getElementById('file_upload');
            const urlInputDiv = document.getElementById('url_input');
            const fileInput = document.getElementById('file');
            const urlInput = document.getElementById('url');

            function toggleFields() {
                const type = resourceType.value;

                if (['pdf', 'docx', 'xlsx', 'pptx', 'ebook'].includes(type)) {
                    fileUploadDiv.style.display = 'block';
                    urlInputDiv.style.display = 'none';
                    fileInput.required = false; // Optional: allow keeping current file
                    urlInput.required = false;
                } else if (type === 'link') {
                    fileUploadDiv.style.display = 'none';
                    urlInputDiv.style.display = 'block';
                    fileInput.required = false;
                    urlInput.required = true;
                } else {
                    fileUploadDiv.style.display = 'none';
                    urlInputDiv.style.display = 'none';
                    fileInput.required = false;
                    urlInput.required = false;
                }
            }

            // Run on page load (important for edit + validation errors)
            toggleFields();

            // Listen for changes
            resourceType.addEventListener('change', toggleFields);
        });

        // Bootstrap custom validation
        (function () {
            'use strict';
            window.addEventListener('load', function () {
                const forms = document.getElementsByClassName('needs-validation');
                Array.prototype.filter.call(forms, function (form) {
                    form.addEventListener('submit', function (event) {
                        if (form.checkValidity() === false) {
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