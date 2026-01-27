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
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>Announcement</h4>
                                <div>
                                    <small class="text-muted">
                                        Posted by <strong>{{ $announcement->user->name }}</strong>
                                        on {{ $announcement->created_at->format('M d, Y \a\t h:i A') }}
                                    </small>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="form-group row mb-4">
                                    <label class="col-form-label text-md-left col-12 col-md-12 col-lg-12 font-weight-bold">Content</label>
                                    <div class="col-sm-12 col-md-12">
                                        <div class="p-4 bg-light rounded border" style="min-height: 200px; line-height: 1.8;">
                                            {!! $announcement->content !!}
                                            <!-- This safely renders HTML from Summernote (bold, italic, lists, links, etc.) -->
                                        </div>
                                    </div>
                                </div>

                                <div class="text-right">
                                    <a href="{{ route('announcements.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left mr-2"></i>Back to Announcements
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    @include('includes.edit_footer')
</body>
</html>