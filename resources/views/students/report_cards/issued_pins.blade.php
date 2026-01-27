@include('includes.head')

<body>
    <div class="loader"></div>
    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            <div class="navbar-bg"></div>
            @include('includes.right_top_nav')
            @include('includes.side_nav')

            <div class="main-content pt-5 mt-5">
                <section class="section mb-5 pb-1 px-0">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>My Issued PINs</h4>
                                <p class="mb-0 text-muted">These are the PINs issued to you for viewing report cards. Each PIN can be used up to <strong>5 times</strong>.</p>
                            </div>
                            <div class="card-body">
                                @if($issuedPins->isEmpty())
                                    <div class="alert alert-info text-center">
                                        <i class="fas fa-info-circle"></i>
                                        No PINs have been issued to you yet.
                                    </div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Session</th>
                                                    <th>Term</th>
                                                    <th>Section</th>
                                                    <th>Class</th>
                                                    <th>PIN Code & Usage</th>
                                                    <th>Issued On</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($issuedPins as $index => $issued)
                                                    @php
                                                        $pin = $issued->pin;
                                                        $usageCount = $pin?->usage_count ?? 0;
                                                        $usesLeft = 5 - $usageCount;
                                                        $isExpired = $usageCount >= 5;
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td><strong>{{ $issued->session->name ?? '-' }}</strong></td>
                                                        <td>{{ $issued->term->name ?? '-' }}</td>
                                                        <td>{{ $issued->section->section_name ?? '-' }}</td>
                                                        <td>{{ $issued->schoolClass->name ?? '-' }}</td>
                                                        <td>
                                                            <div class="d-flex flex-column">
                                                                <!-- PIN Code -->
                                                                <div class="d-flex align-items-center mb-2">
                                                                    <span class="badge badge-lg font-weight-bold mr-2
                                                                        {{ $isExpired ? 'badge-danger' : 'badge-primary' }}">
                                                                        {{ $pin?->pin_code ?? 'N/A' }}
                                                                    </span>

                                                                    <!-- Copy Button (disabled if expired) -->
                                                                    @if(!$isExpired && $pin?->pin_code)
                                                                        <button 
                                                                            class="btn btn-sm btn-icon btn-success copy-pin"
                                                                            data-pin="{{ $pin->pin_code }}"
                                                                            type="button"
                                                                            title="Copy PIN">
                                                                            <i class="fas fa-copy"></i>
                                                                        </button>
                                                                    @endif
                                                                </div>

                                                                <!-- Usage Status -->
                                                                <div class="text-sm">
                                                                    @if($isExpired)
                                                                        <span class="text-danger font-weight-bold">
                                                                            <i class="fas fa-ban"></i> Expired (Used 5/5)
                                                                        </span>
                                                                    @else
                                                                        <span class="text-muted">
                                                                            <i class="fas fa-eye"></i>
                                                                            Used: <strong>{{ $usageCount }}</strong>/5 
                                                                            → <strong class="text-success">{{ $usesLeft }} use{{ $usesLeft == 1 ? '' : 's' }}</strong> left
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>{{ $issued->created_at->format('M d, Y') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="alert alert-warning mt-4">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <strong>Important:</strong> 
                                        Each PIN can only be used <strong>5 times</strong> to view or download your report card. 
                                        Once it reaches 5 uses, it will expire. Keep your PINs safe and do not share them.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        @include('includes.edit_footer')
    </div>

    <!-- Toast Notification -->
    <div id="copyToast" class="toast-notification" style="display: none;">
        <div class="toast-content">
            <i class="fas fa-check-circle text-success"></i>
            <span>PIN copied to clipboard!</span>
        </div>
    </div>
</body>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const copyButtons = document.querySelectorAll('.copy-pin');
        
        copyButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const pin = this.getAttribute('data-pin');
                const icon = this.querySelector('i');
                const toast = document.getElementById('copyToast');

                if (!pin) {
                    alert('No valid PIN to copy.');
                    return;
                }

                // Try modern clipboard API first
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(pin)
                        .then(() => {
                            showSuccess(icon, toast);
                        })
                        .catch(() => {
                            // Fallback to older method
                            fallbackCopy(pin, icon, toast);
                        });
                } else {
                    // Use fallback for older browsers
                    fallbackCopy(pin, icon, toast);
                }
            });
        });

        function showSuccess(icon, toast) {
            // Change icon to check
            icon.classList.remove('fa-copy');
            icon.classList.add('fa-check');

            // Show toast
            toast.style.display = 'block';
            setTimeout(() => {
                toast.style.opacity = '1';
            }, 10);

            // Hide after 3 seconds
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => {
                    toast.style.display = 'none';
                }, 300);
                
                // Reset icon
                icon.classList.remove('fa-check');
                icon.classList.add('fa-copy');
            }, 3000);
        }

        function fallbackCopy(text, icon, toast) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-9999px';
            textArea.style.top = '0';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();

            try {
                const successful = document.execCommand('copy');
                if (successful) {
                    showSuccess(icon, toast);
                } else {
                    alert('Failed to copy PIN. Please copy manually: ' + text);
                }
            } catch (err) {
                alert('Failed to copy PIN. Please copy manually: ' + text);
            }

            document.body.removeChild(textArea);
        }
    });
</script>

<style>
    .toast-notification {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1050;
        background: #28a745;
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .toast-content {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 500;
    }

    .toast-content i {
        font-size: 1.2rem;
    }

    .copy-pin {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        border: none;
        cursor: pointer;
    }

    .copy-pin:hover {
        transform: scale(1.1);
        transition: transform 0.2s;
    }

    .copy-pin:active {
        transform: scale(0.95);
    }

    .text-sm {
        font-size: 0.875rem;
    }

    .badge-lg {
        padding: 0.5rem 0.75rem;
        font-size: 0.95rem;
    }
</style>