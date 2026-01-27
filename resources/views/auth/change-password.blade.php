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
                <section class="section">
                 <div class="section-body">
                        <div class="row">
                            <div class="col-12 col-md-12 col-lg-12 mb-5 pb-5">
                                <div class="card">
                                    <form method="POST" action="{{ route('password.update') }}">
                                        @csrf


                                        <div class="card-header">
                                            <h4>Update Your Password</h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-group col-md-6">
                                                <label>Current Password</label>
                                                <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                                                @error('current_password')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label>New Password</label>
                                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                                                @error('password')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label>Confirm New Password</label>
                                                <input type="password" name="password_confirmation" class="form-control" required>
                                            </div>

                                            <div class="form-group col-md-6">
                                                <button type="submit" class="btn btn-primary">Change Password</button>
                                            
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

   
    @include('includes.footer')
</body>