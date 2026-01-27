@include('includes.head')

<body>
    <div class="loader"></div>
    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            <div class="navbar-bg"></div>
            @include('includes.right_top_nav' )
            @include('includes.side_nav')
            <!-- Main Content -->
            <div class="main-content pt-5 mt-5">
                <section class="section mb-5 pb-1 px-0">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Add Library Book</h4>
                            </div>
                            <form action="{{ route('physical_library.store_book') }}" method="POST"
                                class="needs-validation" novalidate>
                                @csrf


                                <div class="card-body row">
                                    <div class="form-group col-md-6">
                                        <label for="title">Title</label>
                                        <input type="text" class="form-control" id="title" name="title" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="author">Author</label>
                                        <input type="text" class="form-control" id="author" name="author" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="isbn">ISBN</label>
                                        <input type="text" class="form-control" id="isbn" name="isbn">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="quantity">Quantity</label>
                                        <input type="number" class="form-control" id="quantity" name="quantity" min="1"
                                            value="1" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="description">Description</label>
                                        <textarea class="form-control" id="description" name="description"></textarea>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="publisher">Publisher</label>
                                        <input type="text" class="form-control" id="publisher" name="publisher">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="publication_year">Publication Year</label>
                                        <input type="number" class="form-control" id="publication_year"
                                            name="publication_year" min="1800" max="{{ date('Y') }}">
                                    </div>


                                </div>

                                <div class="form-group col-md-6 mb-5 pb-5 mx-2">
                                    <button type="submit" class="btn btn-primary">Add Book</button>
                                </div>
                            </form>

                        </div>
                </section>
            </div>
        </div>
        @include('includes.edit_footer' )