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
                   

                    <!-- Promotions Table -->
                    <div class="card">
                         <div class="section-header">
                        <h1><i class="fas fa-history"></i> Promotion History</h1>
                        <div class="section-header-breadcrumb">
                            <div class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></div>
                            <div class="breadcrumb-item"><a href="{{ route('students.promote') }}">Student Promotion</a></div>
                            <div class="breadcrumb-item active">History</div>
                        </div>
                    </div>

                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    @endif

                    <!-- Summary Cards -->
                    <div class="row mb-4 mx-2">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h3 class="mb-0">{{ $promotions->total() }}</h3>
                                    <small>Total Promotions</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h3 class="mb-0">{{ $promotions->where('status', 'completed')->count() }}</h3>
                                    <small>Completed</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h3 class="mb-0">{{ $promotions->where('status', 'rolled_back')->count() }}</h3>
                                    <small>Rolled Back</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <h3 class="mb-0">{{ $promotions->where('status', 'failed')->count() }}</h3>
                                    <small>Failed</small>
                                </div>
                            </div>
                        </div>
                    </div>
                        <div class="card-header">
                            <h4><i class="fas fa-list"></i> All Promotion Batches</h4>
                            <div class="card-header-action">
                                <a href="{{ route('students.promote') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> New Promotion
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="100">Batch ID</th>
                                            <th>Session</th>
                                            <th>Term</th>
                                            <th>Section</th>
                                            <th>Source Classes</th>
                                            <th>Type</th>
                                            <th width="100" class="text-center">Students</th>
                                            <th width="100" class="text-center">Promoted</th>
                                            <th width="100" class="text-center">Repeating</th>
                                            <th width="80" class="text-center">Rate</th>
                                            <th width="100">Status</th>
                                            <th>Processed By</th>
                                            <th>Date</th>
                                            <th width="180" class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($promotions as $promotion)
                                        <tr>
                                            <td>
                                                <strong class="text-primary">{{ $promotion->promotion_batch_id ?? 'N/A' }}</strong>
                                            </td>
                                            <td>{{ $promotion->session_name ?? 'N/A' }}</td>
                                            <td>{{ $promotion->term_name ?? 'N/A' }}</td>
                                            <td><span class="badge badge-info">{{ $promotion->section_name ?? 'N/A' }}</span></td>
                                            <td><small>{{ $promotion->source_class_names ?? 'N/A' }}</small></td>
                                            <td>
                                                @if($promotion->promotion_type === 'bulk')
                                                <span class="badge badge-secondary"><i class="fas fa-users"></i> Bulk</span>
                                                @else
                                                <span class="badge badge-success"><i class="fas fa-chart-line"></i> Performance</span>
                                                @endif
                                            </td>
                                            <td class="text-center"><strong>{{ $promotion->total_students ?? 0 }}</strong></td>
                                            <td class="text-center">
                                                <span class="badge badge-success">{{ $promotion->promoted_count ?? 0 }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-warning">{{ $promotion->repeating_count ?? 0 }}</span>
                                            </td>
                                            <td class="text-center">
                                                <strong class="text-success">{{ number_format($promotion->promotion_rate ?? 0, 1) }}%</strong>
                                            </td>
                                            <td>
                                                @if($promotion->status === 'completed')
                                                <span class="badge badge-success">
                                                    <i class="fas fa-check-circle"></i> Completed
                                                </span>
                                                @elseif($promotion->status === 'rolled_back')
                                                <span class="badge badge-warning">
                                                    <i class="fas fa-undo"></i> Rolled Back
                                                </span>
                                                @elseif($promotion->status === 'failed')
                                                <span class="badge badge-danger">
                                                    <i class="fas fa-times-circle"></i> Failed
                                                </span>
                                                @else
                                                <span class="badge badge-secondary">
                                                    <i class="fas fa-clock"></i> Pending
                                                </span>
                                                @endif
                                            </td>
                                            <td>
                                                <small>{{ $promotion->processed_by_name ?? 'N/A' }}</small>
                                            </td>
                                            <td>
                                                <small>{{ $promotion->processed_at ? \Carbon\Carbon::parse($promotion->processed_at)->format('M d, Y H:i') : 'N/A' }}</small>
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('students.promotion.details', $promotion->id) }}" 
                                                   class="btn btn-sm btn-info" 
                                                   title="View Details">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                
                                                @if($promotion->status === 'completed')
                                                <button type="button" 
                                                        class="btn btn-sm btn-warning rollback-btn" 
                                                        data-toggle="modal" 
                                                        data-target="#rollbackModal"
                                                        data-promotion-id="{{ $promotion->id }}"
                                                        data-batch-id="{{ $promotion->promotion_batch_id }}"
                                                        data-promoted-count="{{ $promotion->promoted_count }}"
                                                        title="Rollback Promotion">
                                                    <i class="fas fa-undo"></i> Rollback
                                                </button>
                                                @endif
                                            </td>
                                        </tr>

                                        @if($promotion->status === 'rolled_back' && $promotion->rolled_back_at)
                                        <tr class="bg-light">
                                            <td colspan="14" class="p-3">
                                                <div class="alert alert-warning mb-0">
                                                    <strong><i class="fas fa-info-circle"></i> Rollback Information:</strong><br>
                                                    <small>
                                                        Rolled back by <strong>{{ $promotion->rolled_back_by_name ?? 'Unknown' }}</strong> 
                                                        on {{ \Carbon\Carbon::parse($promotion->rolled_back_at)->format('M d, Y H:i') }}<br>
                                                        <strong>Reason:</strong> {{ $promotion->rollback_reason ?? 'No reason provided' }}
                                                    </small>
                                                </div>
                                            </td>
                                        </tr>
                                        @endif
                                        @empty
                                        <tr>
                                            <td colspan="14" class="text-center py-4">
                                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                                <p class="text-muted">No promotion records found</p>
                                                <a href="{{ route('students.promote') }}" class="btn btn-primary">
                                                    <i class="fas fa-plus"></i> Create First Promotion
                                                </a>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @if($promotions->hasPages())
                        <div class="card-footer">
                            {{ $promotions->links() }}
                        </div>
                        @endif
                    </div>
                </section>
            </div>
        </div>
    </div>

    <!-- Single Rollback Modal (Outside Loop) -->
    <div class="modal fade" id="rollbackModal" tabindex="-1" role="dialog" aria-labelledby="rollbackModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="rollbackForm" method="POST" action="">
                    @csrf
                    <div class="modal-header bg-warning text-white">
                        <h5 class="modal-title" id="rollbackModalLabel">
                            <i class="fas fa-exclamation-triangle"></i> Rollback Promotion
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <strong>Warning:</strong> This will restore all promoted students back to their original classes.
                        </div>
                        <p><strong>Batch ID:</strong> <span id="modalBatchId"></span></p>
                        <p><strong>Students to restore:</strong> <span id="modalPromotedCount"></span></p>
                        
                        <div class="form-group">
                            <label>Reason for Rollback <span class="text-danger">*</span></label>
                            <textarea name="rollback_reason" 
                                      id="rollbackReason"
                                      class="form-control" 
                                      rows="3" 
                                      placeholder="Enter reason for rolling back this promotion..."
                                      required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-undo"></i> Confirm Rollback
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('includes.edit_footer')

    <script>
        // Handle rollback button click to populate modal with correct data
        document.addEventListener('DOMContentLoaded', function() {
            const rollbackButtons = document.querySelectorAll('.rollback-btn');
            const rollbackForm = document.getElementById('rollbackForm');
            const modalBatchId = document.getElementById('modalBatchId');
            const modalPromotedCount = document.getElementById('modalPromotedCount');
            const rollbackReason = document.getElementById('rollbackReason');
            
            rollbackButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const promotionId = this.getAttribute('data-promotion-id');
                    const batchId = this.getAttribute('data-batch-id');
                    const promotedCount = this.getAttribute('data-promoted-count');
                    
                    // Update form action with the correct promotion ID
                    rollbackForm.action = "{{ route('students.promotion.rollback', ':id') }}".replace(':id', promotionId);
                    
                    // Update modal content
                    modalBatchId.textContent = batchId;
                    modalPromotedCount.textContent = promotedCount;
                    
                    // Clear previous reason
                    rollbackReason.value = '';
                });
            });
            
            // Clear form when modal is closed
            $('#rollbackModal').on('hidden.bs.modal', function() {
                rollbackReason.value = '';
            });
        });
    </script>
</body>