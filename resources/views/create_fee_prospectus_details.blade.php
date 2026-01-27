{{-- New view: resources/views/create_fee_prospectus_details.blade.php --}}
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
                            <form action="{{ route('fee.prospectus.store') }}" method="POST" class="needs-validation" novalidate>
                                @csrf
                                <input type="hidden" name="section_id" value="{{ $section->id }}">
                                <input type="hidden" name="term_id" value="{{ $term->id }}">
                                @foreach($classes as $cls)
                                    <input type="hidden" name="class_ids[]" value="{{ $cls->id }}">
                                @endforeach

                                <div class="card-header">
                                    <h4>Create Fee Prospectus for {{ $classes->pluck('name')->implode(', ') }} - {{ $section->section_name }} ({{ $term->name }})</h4>
                                </div>
                                <div class="card-body">
                                    <div id="fees-container">
                                        <div class="row mb-3 fee-row">
                                            <div class="col-md-5">
                                                <input type="text" class="form-control" name="fees[0][item]" placeholder="Item (e.g., Tuition Fee)" required>
                                            </div>
                                            <div class="col-md-5">
                                                <input type="number" class="form-control amount-input" name="fees[0][amount]" placeholder="Amount" min="0" step="0.01" required>
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-danger remove-fee" style="display: none;">Remove</button>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" id="add-fee-row" class="btn btn-secondary mb-3">Add Fee Item</button>
                                    <div class="row">
                                        <div class="col-md-10"></div>
                                        <div class="col-md-2">
                                            <strong>Total: ₦<span id="total-amount">0.00</span></strong>
                                        </div>
                                    </div>
                                    <!-- Submit Button -->
                                    <div class="form-group mt-4 pt-4">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Save Prospectus
                                        </button>
                                        <a href="{{ route('fee.prospectus.create') }}" class="btn btn-light">Back</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        @include('includes.edit_footer')

        <script>
            let feeRowIndex = 1;

            $(document).ready(function() {
                // Calculate total on amount change
                $(document).on('input', '.amount-input', function() {
                    calculateTotal();
                });

                // Add new fee row
                $('#add-fee-row').click(function() {
                    const newRow = `
                        <div class="row mb-3 fee-row">
                            <div class="col-md-5">
                                <input type="text" class="form-control" name="fees[${feeRowIndex}][item]" placeholder="Item" required>
                            </div>
                            <div class="col-md-5">
                                <input type="number" class="form-control amount-input" name="fees[${feeRowIndex}][amount]" placeholder="Amount" min="0" step="0.01" required>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-danger remove-fee">Remove</button>
                            </div>
                        </div>
                    `;
                    $('#fees-container').append(newRow);
                    feeRowIndex++;
                    calculateTotal();
                });

                // Remove fee row
                $(document).on('click', '.remove-fee', function() {
                    $(this).closest('.fee-row').remove();
                    calculateTotal();
                });

                function calculateTotal() {
                    let total = 0;
                    $('.amount-input').each(function() {
                        const amount = parseFloat($(this).val()) || 0;
                        total += amount;
                    });
                    $('#total-amount').text(total.toFixed(2));
                }
            });
        </script>
    </body>
</body>