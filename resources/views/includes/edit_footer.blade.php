<footer class="main-footer">
    <div class="footer-right">
        <a href="">All Rights Reserved</a>
    </div>
</footer>
</div>
</div>

<!-- General JS Scripts -->
<script src="{{ asset('js/app.min.js') }}"></script>

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

<script>
(function() {
  'use strict';
  
  // Unique namespace to prevent conflicts
  const __formSubmitHandler__ = {
    processingClass: '__fshs-processing__',
    originalTextAttr: 'data-__fshs-original-text__',
    timeoutIdAttr: 'data-__fshs-timeout-id__',
    submitDuration: 15000, // 15 seconds
    
    // Create spinner HTML
    createSpinner: function() {
      return '<span class="__fshs-spinner__" style="display: inline-block; width: 14px; height: 14px; border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff; border-radius: 50%; animation: __fshs-spin__ 0.6s linear infinite; margin-right: 5px;"></span>';
    },
    
    // Initialize spinner animation
    initSpinnerStyles: function() {
      if (document.getElementById('__fshs-styles__')) return;
      
      const __fshs_styleElement__ = document.createElement('style');
      __fshs_styleElement__.id = '__fshs-styles__';
      __fshs_styleElement__.textContent = `
        @keyframes __fshs-spin__ {
          0% { transform: rotate(0deg); }
          100% { transform: rotate(360deg); }
        }
        .__fshs-processing__ {
          opacity: 0.7;
          cursor: not-allowed !important;
          pointer-events: none;
        }
      `;
      document.head.appendChild(__fshs_styleElement__);
    },
    
    // Disable button with spinner
    disableButton: function(__fshs_button__) {
      if (!__fshs_button__ || __fshs_button__.classList.contains(this.processingClass)) {
        return;
      }
      
      // Store original text
      const __fshs_originalText__ = __fshs_button__.innerHTML;
      __fshs_button__.setAttribute(this.originalTextAttr, __fshs_originalText__);
      
      // Add spinner and processing state
      __fshs_button__.innerHTML = this.createSpinner() + 'Processing...';
      __fshs_button__.classList.add(this.processingClass);
      __fshs_button__.disabled = true;
      
      // Set timeout to re-enable button
      const __fshs_timeoutId__ = setTimeout(() => {
        this.enableButton(__fshs_button__);
      }, this.submitDuration);
      
      __fshs_button__.setAttribute(this.timeoutIdAttr, __fshs_timeoutId__);
    },
    
    // Re-enable button
    enableButton: function(__fshs_button__) {
      if (!__fshs_button__) return;
      
      // Clear timeout if exists
      const __fshs_timeoutId__ = __fshs_button__.getAttribute(this.timeoutIdAttr);
      if (__fshs_timeoutId__) {
        clearTimeout(parseInt(__fshs_timeoutId__));
        __fshs_button__.removeAttribute(this.timeoutIdAttr);
      }
      
      // Restore original text
      const __fshs_originalText__ = __fshs_button__.getAttribute(this.originalTextAttr);
      if (__fshs_originalText__) {
        __fshs_button__.innerHTML = __fshs_originalText__;
        __fshs_button__.removeAttribute(this.originalTextAttr);
      }
      
      // Remove processing state
      __fshs_button__.classList.remove(this.processingClass);
      __fshs_button__.disabled = false;
    },
    
    // Initialize form handlers
    init: function() {
      this.initSpinnerStyles();
      
      // Get all forms
      const __fshs_allForms__ = document.querySelectorAll('form');
      
      __fshs_allForms__.forEach((__fshs_form__) => {
        // Skip if already initialized
        if (__fshs_form__.hasAttribute('data-__fshs-initialized__')) {
          return;
        }
        
        __fshs_form__.setAttribute('data-__fshs-initialized__', 'true');
        
        __fshs_form__.addEventListener('submit', (__fshs_event__) => {
          // Use setTimeout to check if form actually submits
          // If preventDefault() was called or validation fails, this won't execute
          setTimeout(() => {
            // Check if form is still in the document and wasn't prevented
            if (!__fshs_event__.defaultPrevented && document.contains(__fshs_form__)) {
              // Find all submit buttons in this form
              const __fshs_submitButtons__ = __fshs_form__.querySelectorAll(
                'button[type="submit"], input[type="submit"], button:not([type])'
              );
              
              // Disable all submit buttons
              __fshs_submitButtons__.forEach((__fshs_btn__) => {
                this.disableButton(__fshs_btn__);
              });
            }
          }, 0);
          
          // Re-enable buttons if validation fails
          __fshs_form__.addEventListener('invalid', () => {
            const __fshs_submitButtons__ = __fshs_form__.querySelectorAll(
              'button[type="submit"], input[type="submit"], button:not([type])'
            );
            __fshs_submitButtons__.forEach((__fshs_btn__) => {
              this.enableButton(__fshs_btn__);
            });
          }, { once: true });
          
        }, false);
      });
    }
  };
  
  // Initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
      __formSubmitHandler__.init();
    });
  } else {
    __formSubmitHandler__.init();
  }
  
  // Expose re-initialization function for dynamically added forms
  window.__reinitFormSubmitHandler__ = function() {
    __formSubmitHandler__.init();
  };
  
})();
</script>

</body>
</html>