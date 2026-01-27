</div>
<footer class="main-footer">
    <div class="footer-right">
        <a href="" class="px-4">All Rights Reserved</a>
    </div>
</footer>
</div>
</div>

<!-- General JS Scripts -->
<!-- {{-- <script src="{{ asset('js/app.min.js') }}"></script> --}} -->

<!-- JS Libraries -->
<script src="{{ asset('bundles/summernote/summernote-bs4.js') }}"></script>

<!-- Template JS File -->
<script src="{{ asset('js/scripts.js') }}"></script>

<!-- Custom JS File -->
<script src="{{ asset('js/custom.js') }}"></script>

<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

<!-- MathJax -->
<script src="https://polyfill.io/v3/polyfill.min.js?features=es6"></script>
<script id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>

<!-- Toastr Configuration and Notifications -->
<script>
    // Toastr Configuration
    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: 'toast-top-right',
        timeOut: 5000,
        extendedTimeOut: 1000,
        showEasing: 'swing',
        hideEasing: 'linear',
        showMethod: 'fadeIn',
        hideMethod: 'fadeOut'
    };

    // Display Success Notification
    @if (session('success'))
        toastr.success("{{ session('success') }}");
    @endif

    // Display Error Notification
    @if (session('error'))
        toastr.error("{{ session('error') }}");
    @endif

    // Display Info Notification
    @if (session('info'))
        toastr.info("{{ session('info') }}");
    @endif

    // Display Validation Errors
    @if ($errors->any())
        @foreach ($errors->all() as $error)
            toastr.error("{{ $error }}");
        @endforeach
    @endif
</script>

</body>
</html>