<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>{{ school_name() ?? 'SMS' }} - Login</title>
    
    <!-- General CSS Files -->
    <link rel="stylesheet" href="{{ asset('css/app.min.css') }}">
    <link rel="stylesheet" href="{{ asset('bundles/bootstrap-social/bootstrap-social.css') }}">
    <!-- Template CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}">
    <!-- Custom style CSS -->
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    
    <!-- Dynamic Favicon -->
    <link rel='shortcut icon' type='image/x-icon' href="{{ school_logo() }}" />
    
    <!-- Toastr CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet">
</head>

<body>
    <div class="loader"></div>
    <div id="app">
        <section class="section">
            <div class="container">
                <div class="row">
                    <div class="col-12 col-sm-8 offset-sm-2 col-md-6 offset-md-3 col-lg-6 offset-lg-3 col-xl-4 offset-xl-4 mt-5">
                        <div class="card card-primary">
                            <div class="card-body">
                                <form method="POST" action="{{ route('login.submit') }}" class="needs-validation" novalidate="">
                                    @csrf

                                    <div class="text-center mb-4">
                                        <!-- Dynamic Logo -->
                                        <img src="{{ school_logo() }}" 
                                             alt="{{ school_name() ?? 'School Logo' }}" 
                                             style="width: 80px; height:80px; object-fit: contain;">
                                        
                                        <!-- School Name (Optional) -->
                                        <h6 class="mt-2">{{ school_name() }}</h6>
                                    </div>

                                    <div class="form-group mb-1">
                                        <label for="user_type">User Type</label>
                                        <select id="user_type" class="form-control" name="user_type" tabindex="3" required>
                                            <option value="" disabled selected>Select User Type</option>
                                            <option value="admin">Admin</option>
                                            <option value="staff">Staff</option>
                                            <option value="student">Student</option>
                                            <option value="parent">Parent</option>
                                        </select>
                                        <div class="invalid-feedback">
                                            Please select your user type
                                        </div>
                                    </div>

                                    <div class="form-group mb-1">
                                        <label for="email">Email</label>
                                        <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" tabindex="1" autocomplete="username" autofocus>
                                        <div class="invalid-feedback">
                                            Please fill in your email
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="d-block">
                                            <label for="password" class="control-label">Password</label>
                                        </div>
                                        <input id="password" type="password" class="form-control" name="password" tabindex="2" autocomplete="current-password">
                                        <div class="invalid-feedback">
                                            Please fill in your password
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="remember-me" tabindex="4">
                                            <label class="custom-control-label" for="remember-me">Remember me on this device</label>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary btn-lg btn-block" tabindex="5">
                                            Login
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- General JS Scripts -->
    <script src="{{ asset('js/app.min.js') }}"></script>
    <!-- Template JS File -->
    <script src="{{ asset('js/scripts.js') }}"></script>
    <!-- Custom JS File -->
    <script src="{{ asset('js/custom.js') }}"></script>

    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

    <!-- "Remember me": keep user type + email on this device. The password is
         deliberately never stored by the page - the browser's password manager
         handles that (see the autocomplete attributes on the fields). -->
    <script>
        (function () {
            var STORAGE_KEY = 'loginRemembered';
            var form     = document.querySelector('form[action="{{ route('login.submit') }}"]');
            var email    = document.getElementById('email');
            var userType = document.getElementById('user_type');
            var remember = document.getElementById('remember-me');
            var password = document.getElementById('password');

            function readSaved() {
                try { return JSON.parse(localStorage.getItem(STORAGE_KEY)); } catch (e) { return null; }
            }

            var saved = readSaved();
            var typeIsValid = saved && Array.prototype.some.call(userType.options, function (option) {
                return option.value === saved.user_type;
            });

            if (saved && saved.email && typeIsValid) {
                if (!email.value) { email.value = saved.email; }
                userType.value = saved.user_type;
                remember.checked = true;
                password.focus();
            }

            form.addEventListener('submit', function () {
                try {
                    if (remember.checked) {
                        localStorage.setItem(STORAGE_KEY, JSON.stringify({
                            email: email.value.trim(),
                            user_type: userType.value
                        }));
                    } else {
                        localStorage.removeItem(STORAGE_KEY);
                    }
                } catch (e) { /* storage unavailable (private mode) - just skip remembering */ }
            });
        })();
    </script>

    <!-- Display Toastr Notifications -->
    @if(session('error'))
        <script>
            toastr.error("{{ session('error') }}");
        </script>
    @elseif(session('success'))
        <script>
            toastr.success("{{ session('success') }}");
        </script>
    @elseif(session('info'))
        <script>
            toastr.info("{{ session('info') }}");
        </script>
    @endif
</body>

</html>