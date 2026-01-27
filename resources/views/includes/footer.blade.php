<footer class="main-footer">
  <div class="footer-right">
    <a href="">All Rights Reserved</a></a>
  </div>
  <div class="footer-right">
  </div>
</footer>
</div>
</div>





<script>
$(document).ready(function() {
  $('.summernote').summernote({
    height: 200,
    callbacks: {
      onChange: function(contents, $editable) {
        MathJax.typesetPromise();
      }
    }
  });
});


</script>

<script>
  // Add this to your JavaScript file
$(document).ready(function() {
    // Mark all as read
    $('#markAllRead').on('click', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: "{{ route('announcements.markAllRead') }}",
            type: 'GET',
            success: function(response) {
                // Remove unread indicators
                $('.notification-badge').remove();
                // You might want to add visual indication that items were read
                // For example, change background color of unread items
                
                // Show a notification if you want
                toastr.success('All notifications marked as read');
            }
        });
    });
    
    // Mark individual announcement as read when clicked
    $('.dropdown-list-content a').on('click', function() {
        let announcementId = $(this).data('announcement-id');
        
        // Only make the request if we have an ID
        if (announcementId) {
            $.ajax({
                url: `/announcements/${announcementId}/mark-read`,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    // Update unread count if needed
                }
            });
        }
    });
});
</script>




<!--  
  <script src="js/app.min.js"></script>

  <script src="bundles/apexcharts/apexcharts.min.js"></script>
  
  <script src="js/page/index.js"></script>

  <script src="js/scripts.js"></script>
  
  <script src="js/custom.js"></script> -->


<script src="js/app.min.js"></script>
<!-- JS Libraies -->
<!-- Page Specific JS File -->
<script src="{{ asset('bundles/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('bundles/datatables/export-tables/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('bundles/datatables/export-tables/buttons.flash.min.js') }}"></script>
<script src="{{ asset('bundles/datatables/export-tables/jszip.min.js') }}"></script>
<script src="{{ asset('bundles/datatables/export-tables/pdfmake.min.js') }}"></script>
<script src="{{ asset('bundles/datatables/export-tables/vfs_fonts.js') }}"></script>
<script src="{{ asset('bundles/datatables/export-tables/buttons.print.min.js') }}"></script>
<script src="{{ asset('js/page/datatables.js') }}"></script>

<!-- Template JS File -->
<script src="{{ asset('js/scripts.js') }}"></script>

<!-- Custom JS File -->
<script src="{{ asset('js/custom.js') }}"></script>


<script>
  document.addEventListener("DOMContentLoaded", function() {
    const currentPage = window.location.pathname.split("/").pop(); // e.g., "dashboard.php"
    const navLinks = document.querySelectorAll(".sidebar-menu a.nav-link");

    navLinks.forEach(link => {
      const href = link.getAttribute("href");
      if (href === currentPage) {
        // Add 'active' class to the link
        link.classList.add("active");

        // Also add 'active' class to the parent <li class="dropdown">
        const dropdown = link.closest(".dropdown");
        if (dropdown) {
          dropdown.classList.add("active");
        }
      }
    });
  });
</script>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    const sidebar = document.querySelector('.main-sidebar');

    function lockSidebar() {
      if (!sidebar) return;

      sidebar.style.position = 'fixed';
      sidebar.style.top = '0';
      sidebar.style.bottom = '0';
      sidebar.style.height = '100vh';
      sidebar.style.overflowY = 'auto';
      sidebar.style.zIndex = '999';
      sidebar.style.background = '#fff'; // optional: avoid transparency in mini
    }

    lockSidebar();

    // Observe class changes to re-apply lock if needed
    const observer = new MutationObserver(() => {
      lockSidebar();
    });

    observer.observe(document.body, {
      attributes: true,
      attributeFilter: ['class']
    });

    window.addEventListener('resize', lockSidebar);
  });
</script>

<!-- Add this just before closing </body> tag for Toastr scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

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



<!-- <script>
  $(document).ready(function() {
    $('#tableExport').DataTable({
      paging: false, // Disable DataTables pagination
      info: false, // Hide entry info
      searching: true, // Optional: enable search
      ordering: true, // Enable column sort
      destroy: true, // Prevent "Cannot reinitialize" error
      dom: 'Bfrtip', // Add export buttons
      buttons: [
        'csv', 'excel', 'pdf', 'print'
      ]
    });
  });
</script> -->



<script src="https://polyfill.io/v3/polyfill.min.js?features=es6"></script>
<script id="MathJax-script" async
  src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js">
</script>
</body>

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

<!-- index.html  21 Nov 2019 03:47:04 GMT -->

</html>