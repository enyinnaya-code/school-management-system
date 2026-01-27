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
                                <h4>Wards' Issued PINs</h4>
                                <p class="mb-0 text-muted">
                                    These are the PINs issued to your wards for viewing their report cards. 
                                    Each PIN can be used up to <strong>5 times</strong>.
                                </p>
                            </div>
                            <div class="card-body">
                                @if($issuedPins->isEmpty())
                                    <div class="alert alert-info text-center">
                                        <i class="fas fa-info-circle"></i>
                                        No PINs have been issued to your wards yet.
                                    </div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Ward</th>
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
                                                        <td>
                                                            <strong>{{ $issued->student->name }}</strong>
                                                            <br>
                                                            <small class="text-muted">Adm: {{ $issued->student->admission_no ?? '-' }}</small>
                                                        </td>
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

                                                                    <!-- Copy Button (only if not expired) -->
                                                                    @if(!$isExpired && $pin?->pin_code)
                                                                        <button 
                                                                            class="btn btn-sm btn-icon btn-success copy-pin"
                                                                            data-pin="{{ $pin->pin_code }}"
                                                                            title="Copy PIN for {{ $issued->student->name }}">
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
                                        Each PIN can only be used <strong>5 times</strong> to view or download your ward's report card. 
                                        Once it reaches 5 uses, it will expire. Please keep these PINs safe and use them responsibly.
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
    <div class="toast toast-success" role="alert" aria-live="assertive" aria-atomic="true"
         style="position: fixed; bottom: 20px; right: 20px; z-index: 1050; display: none;">
        <div class="toast-header">
            <i class="fas fa-check-circle text-success mr-2"></i>
            <strong class="mr-auto">Copied!</strong>
            <button type="button" class="ml-2 mb-1 close" data-dismiss="toast">
                <span aria-hidden="true">×</span>
            </button>
        </div>
        <div class="toast-body">
            PIN has been copied to clipboard.
        </div>
    </div>
</body>

<script>
    document.querySelectorAll('.copy-pin').forEach(button => {
        button.addEventListener('click', function () {
            const pin = this.getAttribute('data-pin');

            if (!pin) {
                alert('No valid PIN to copy.');
                return;
            }

            navigator.clipboard.writeText(pin).then(() => {
                const icon = this.querySelector('i');
                icon.classList.remove('fa-copy');
                icon.classList.add('fa-check');

                const toast = document.querySelector('.toast');
                toast.style.display = 'block';

                setTimeout(() => {
                    toast.style.display = 'none';
                    icon.classList.remove('fa-check');
                    icon.classList.add('fa-copy');
                }, 3000);
            }).catch(() => {
                alert('Failed to copy PIN. Please copy manually.');
            });
        });
    });
</script>

<style>
    .toast {
        min-width: 300px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .copy-pin {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .copy-pin:hover {
        transform: scale(1.1);
        transition: transform 0.2s;
    }
    .text-sm {
        font-size: 0.875rem;
    }
</style>