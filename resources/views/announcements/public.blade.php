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
                                <h4>All Announcements</h4>
                            </div>
                            <div class="card-body">
                                @if($announcements->count() > 0)
                                    <div class="list-group">
                                        @foreach($announcements as $announcement)
                                            @php
                                                $isUnread = Auth::user()->announcements()
                                                    ->where('announcement_id', $announcement->id)
                                                    ->wherePivot('read_at', null)
                                                    ->exists();
                                            @endphp

                                            <a href="{{ route('announcements.show', $announcement) }}"
                                               class="list-group-item list-group-item-action flex-column align-items-start {{ $isUnread ? 'border-primary' : '' }}"
                                               style="{{ $isUnread ? 'background-color: rgba(13, 110, 253, 0.05);' : '' }}">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h5 class="mb-1 font-weight-bold">
                                                        {{ $announcement->user->name }}
                                                        @if($isUnread)
                                                            <span class="badge badge-primary ml-2">New</span>
                                                        @endif
                                                    </h5>
                                                    <small class="text-muted">
                                                        {{ $announcement->created_at->format('M d, Y \a\t h:i A') }}
                                                    </small>
                                                </div>
                                                <div class="mb-1">
                                                    {!! Str::limit(strip_tags($announcement->content), 200) !!}
                                                </div>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock"></i> {{ $announcement->created_at->diffForHumans() }}
                                                </small>
                                            </a>
                                        @endforeach
                                    </div>

                                    <div class="mt-4">
                                        {{ $announcements->links() }}
                                    </div>
                                @else
                                    <div class="text-center py-5">
                                        <i class="fas fa-bullhorn fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No announcements have been posted yet.</p>
                                    </div>
                                @endif
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