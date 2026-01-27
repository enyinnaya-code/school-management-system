@include('includes.head')
<style>
    .table:not(.table-sm):not(.table-md):not(.dataTable) td,
    .table:not(.table-sm):not(.table-md):not(.dataTable) th {
        padding: 0 5px;
        height: 40px;

    }
</style>

<body>
    <div class="loader"></div>
    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            <div class="navbar-bg"></div>
            @include('includes.right_top_nav')
            @include('includes.side_nav')

            <div class="main-content pt-5 mt-5 mb-5 pb-3">
                <section class="section">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>
                                    Affective & Psychomotor Skills - {{ $student->name }}
                                    <small class="text-dark">({{ $class->name }} - {{ $section->section_name
                                        }})</small>
                                </h4>
                                
                            </div>

                            <div class="card-body">
                                <div class="alert alert-info mb-4">
                                    <strong>Session:</strong> {{ $currentSession->name }} |
                                    <strong>Term:</strong> {{ $currentTerm->name }}
                                    <br><small>Rate each skill from 1 (Poor) to 5 (Excellent)</small>
                                </div>

                                <form action="{{ route('results.remarks.update', $student->id) }}" method="POST">
                                    @csrf

                                    <div class="row">
                                        <!-- Affective Skills Table -->
                                        <div class="col-md-6">
                                            <h6 class="mb-3 text-lef font-weight-bold">AFFECTIVE SKILLS</h6>
                                            <div class="table-responsive mb-5">
                                                <table class="table table-bordered table-hover text-center">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th>Skill</th>
                                                            <th>1</th>
                                                            <th>2</th>
                                                            <th>3</th>
                                                            <th>4</th>
                                                            <th>5</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td><strong>Punctuality</strong></td>@for($i=1;$i
                                                            <=5;$i++)<td>
                                                                <input type="radio" name="affective[punctuality]"
                                                                    value="{{ $i }}" {{
                                                                    ($remark->affective_ratings['punctuality'] ?? null)
                                                                ==
                                                                $i
                                                                ? 'checked' : '' }} required></td>@endfor
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Politeness</strong></td>@for($i=1;$i
                                                            <=5;$i++)<td>
                                                                <input type="radio" name="affective[politeness]"
                                                                    value="{{ $i }}" {{
                                                                    ($remark->affective_ratings['politeness'] ?? null)
                                                                == $i
                                                                ? 'checked' : '' }} required></td>@endfor
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Neatness</strong></td>@for($i=1;$i<=5;$i++)<td>
                                                                <input type="radio" name="affective[neatness]"
                                                                    value="{{ $i }}" {{
                                                                    ($remark->affective_ratings['neatness'] ?? null) ==
                                                                $i ?
                                                                'checked' : '' }} required></td>@endfor
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Honesty</strong></td>@for($i=1;$i<=5;$i++)<td>
                                                                <input type="radio" name="affective[honesty]"
                                                                    value="{{ $i }}" {{
                                                                    ($remark->affective_ratings['honesty'] ?? null) ==
                                                                $i
                                                                ?
                                                                'checked' : '' }} required></td>@endfor
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Leadership Skill</strong></td>@for($i=1;$i
                                                            <=5;$i++)<td>
                                                                <input type="radio" name="affective[leadership_skill]"
                                                                    value="{{ $i }}" {{
                                                                    ($remark->affective_ratings['leadership_skill'] ??
                                                                null)
                                                                ==
                                                                $i ? 'checked' : '' }} required></td>@endfor
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Cooperation</strong></td>@for($i=1;$i
                                                            <=5;$i++)<td>
                                                                <input type="radio" name="affective[cooperation]"
                                                                    value="{{ $i }}" {{
                                                                    ($remark->affective_ratings['cooperation'] ?? null)
                                                                ==
                                                                $i
                                                                ? 'checked' : '' }} required></td>@endfor
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Attentiveness</strong></td>@for($i=1;$i
                                                            <=5;$i++)<td>
                                                                <input type="radio" name="affective[attentiveness]"
                                                                    value="{{ $i }}" {{
                                                                    ($remark->affective_ratings['attentiveness'] ??
                                                                null) ==
                                                                $i
                                                                ? 'checked' : '' }} required></td>@endfor
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Perseverance</strong></td>@for($i=1;$i
                                                            <=5;$i++)<td>
                                                                <input type="radio" name="affective[perseverance]"
                                                                    value="{{ $i }}" {{
                                                                    ($remark->affective_ratings['perseverance'] ?? null)
                                                                ==
                                                                $i ?
                                                                'checked' : '' }} required></td>@endfor
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Attitude to Work</strong></td>@for($i=1;$i
                                                            <=5;$i++)<td>
                                                                <input type="radio" name="affective[attitude_to_work]"
                                                                    value="{{ $i }}" {{
                                                                    ($remark->affective_ratings['attitude_to_work'] ??
                                                                null)
                                                                ==
                                                                $i ? 'checked' : '' }} required></td>@endfor
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <!-- Psychomotor Skills Table -->
                                        <div class="col-md-6">
                                            <h6 class="mb-3 text-left font-weight-bold">PSYCHOMOTOR SKILLS</h6>
                                            <div class="table-responsive mb-5">
                                                <table class="table table-bordered table-hover text-center">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th>Skill</th>
                                                            <th>1</th>
                                                            <th>2</th>
                                                            <th>3</th>
                                                            <th>4</th>
                                                            <th>5</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td><strong>Handwriting</strong></td>@for($i=1;$i
                                                            <=5;$i++)<td>
                                                                <input type="radio" name="psychomotor[handwriting]"
                                                                    value="{{ $i }}" {{
                                                                    ($remark->psychomotor_ratings['handwriting'] ??
                                                                null) ==
                                                                $i
                                                                ? 'checked' : '' }} required></td>@endfor
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Verbal Fluency</strong></td>@for($i=1;$i
                                                            <=5;$i++)<td>
                                                                <input type="radio" name="psychomotor[verbal_fluency]"
                                                                    value="{{ $i }}" {{
                                                                    ($remark->psychomotor_ratings['verbal_fluency'] ??
                                                                null)
                                                                ==
                                                                $i ? 'checked' : '' }} required></td>@endfor
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Sports</strong></td>@for($i=1;$i<=5;$i++)<td>
                                                                <input type="radio" name="psychomotor[sports]"
                                                                    value="{{ $i }}" {{
                                                                    ($remark->psychomotor_ratings['sports'] ?? null) ==
                                                                $i ?
                                                                'checked' : '' }} required></td>@endfor
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Handling Tools</strong></td>@for($i=1;$i
                                                            <=5;$i++)<td>
                                                                <input type="radio" name="psychomotor[handling_tools]"
                                                                    value="{{ $i }}" {{
                                                                    ($remark->psychomotor_ratings['handling_tools'] ??
                                                                null)
                                                                ==
                                                                $i ? 'checked' : '' }} required></td>@endfor
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Drawing & Painting</strong></td>@for($i=1;$i
                                                            <=5;$i++)<td><input type="radio"
                                                                    name="psychomotor[drawing_painting]"
                                                                    value="{{ $i }}" {{
                                                                    ($remark->psychomotor_ratings['drawing_painting'] ??
                                                                null)
                                                                == $i ? 'checked' : '' }} required></td>@endfor
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Remarks -->
                                    <div class="form-group">
                                        <label><strong>Class Teacher's Remark</strong></label>
                                        <textarea name="teacher_remark" class="form-control" rows="4"
                                            required>{{ old('teacher_remark', $remark->teacher_remark) }}</textarea>
                                    </div>

                                    @if(in_array(Auth::user()->user_type, [1,2]))
                                    <div class="form-group">
                                        <label><strong>Principal's Remark</strong></label>
                                        <textarea name="principal_remark" class="form-control"
                                            rows="4">{{ old('principal_remark', $remark->principal_remark) }}</textarea>
                                    </div>
                                    @endif

                                    <div class="text-center">
                                        <button type="submit" class="btn btn-success btn-lg px-5">
                                            <i class="fas fa-save"></i> Save All Ratings & Remarks
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    @include('includes.edit_footer')
</body>