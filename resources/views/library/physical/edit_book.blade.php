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
                                <h4>Edit Book</h4>
                            </div>

                            <form
                                action="{{ route('physical_library.update_book', ['encryptedId' => Crypt::encrypt($book->id)]) }}"
                                method="POST" class="needs-validation" novalidate>
                                @csrf
                                @method('PUT')


                                <div class="card-body">
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label for="title">Title <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="title" name="title"
                                                value="{{ old('title', $book->title) }}" required>
                                            <div class="invalid-feedback">Please enter a title.</div>
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="author">Author <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="author" name="author"
                                                value="{{ old('author', $book->author) }}" required>
                                            <div class="invalid-feedback">Please enter an author.</div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label for="isbn">ISBN</label>
                                            <input type="text" class="form-control" id="isbn" name="isbn"
                                                value="{{ old('isbn', $book->isbn) }}"
                                                placeholder="e.g. 978-3-16-148410-0">
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="quantity">Total Quantity <span
                                                    class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="quantity" name="quantity"
                                                value="{{ old('quantity', $book->quantity) }}" min="1" required>
                                            <small class="text-muted">Current available: {{ $book->available_quantity
                                                }}</small>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label for="publisher">Publisher</label>
                                            <input type="text" class="form-control" id="publisher" name="publisher"
                                                value="{{ old('publisher', $book->publisher) }}">
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="publication_year">Publication Year</label>
                                            <input type="number" class="form-control" id="publication_year"
                                                name="publication_year"
                                                value="{{ old('publication_year', $book->publication_year) }}"
                                                min="1800" max="{{ date('Y') }}" placeholder="e.g. 2023">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="description">Description</label>
                                        <textarea class="form-control" id="description" name="description"
                                            rows="4">{{ old('description', $book->description) }}</textarea>
                                    </div>
                                </div>

                                <div class="card-footer text-right pt-4">
                                    <a href="{{ route('physical_library.manage_books') }}"
                                        class="btn btn-secondary mr-2">
                                        Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        Update Book
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
</body>

<script>
    // Form validation feedback
    (function() {
        'use strict';
        window.addEventListener('load', function() {
            const forms = document.getElementsByClassName('needs-validation');
            Array.prototype.filter.call(forms, function(form) {
                form.addEventListener('submit', function(event) {
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