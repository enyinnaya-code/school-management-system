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
                                    <small class="text-dark">({{ $class->name }} - {{ $section->section_name }})</small>
                                </h4>
                            </div>

                            <div class="card-body">
                                <div class="alert alert-info mb-4">
                                    <strong>Session:</strong> {{ $currentSession->name }} |
                                    <strong>Term:</strong> {{ $currentTerm->name }}
                                    <br><small>Rate each skill from 1 (Poor) to 5 (Excellent)</small>
                                </div>

                                @php
                                    // Determine if this class is a Primary class
                                    $isPrimary = \Illuminate\Support\Facades\DB::table('primary_result_classes')
                                        ->where('school_class_id', $class->id)
                                        ->exists();
                                @endphp

                                <form action="{{ route('results.remarks.update', $student->id) }}" method="POST">
                                    @csrf

                                    <div class="row">
                                        <!-- Affective Skills Table -->
                                        <div class="col-md-6">
                                            <h6 class="mb-3 text-left font-weight-bold">AFFECTIVE AREA</h6>
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
                                                            <td><strong>Punctuality</strong></td>
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <td><input type="radio" name="affective[punctuality]" value="{{ $i }}" {{ ($remark->affective_ratings['punctuality'] ?? null) == $i ? 'checked' : '' }}></td>
                                                            @endfor
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Politeness</strong></td>
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <td><input type="radio" name="affective[politeness]" value="{{ $i }}" {{ ($remark->affective_ratings['politeness'] ?? null) == $i ? 'checked' : '' }}></td>
                                                            @endfor
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Neatness</strong></td>
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <td><input type="radio" name="affective[neatness]" value="{{ $i }}" {{ ($remark->affective_ratings['neatness'] ?? null) == $i ? 'checked' : '' }}></td>
                                                            @endfor
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Honesty</strong></td>
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <td><input type="radio" name="affective[honesty]" value="{{ $i }}" {{ ($remark->affective_ratings['honesty'] ?? null) == $i ? 'checked' : '' }}></td>
                                                            @endfor
                                                        </tr>
                                                        <tr>
                                                            name="affective[leadership_skill]" <td><strong>Leadership Skill</strong></td>
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <td><input type="radio" name="affective[leadership_skill]" value="{{ $i }}" {{ ($remark->affective_ratings['leadership_skill'] ?? null) == $i ? 'checked' : '' }}></td>
                                                            @endfor
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Cooperation</strong></td>
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <td><input type="radio" name="affective[cooperation]" value="{{ $i }}" {{ ($remark->affective_ratings['cooperation'] ?? null) == $i ? 'checked' : '' }}></td>
                                                            @endfor
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Attentiveness</strong></td>
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <td><input type="radio" name="affective[attentiveness]" value="{{ $i }}" {{ ($remark->affective_ratings['attentiveness'] ?? null) == $i ? 'checked' : '' }}></td>
                                                            @endfor
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Perseverance</strong></td>
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <td><input type="radio" name="affective[perseverance]" value="{{ $i }}" {{ ($remark->affective_ratings['perseverance'] ?? null) == $i ? 'checked' : '' }}></td>
                                                            @endfor
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Attitude to Work</strong></td>
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <td><input type="radio" name="affective[attitude_to_work]" value="{{ $i }}" {{ ($remark->affective_ratings['attitude_to_work'] ?? null) == $i ? 'checked' : '' }}></td>
                                                            @endfor
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Helping Other</strong></td>
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <td><input type="radio" name="affective[helping_other]" value="{{ $i }}" {{ ($remark->affective_ratings['helping_other'] ?? null) == $i ? 'checked' : '' }}></td>
                                                            @endfor
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Emotional Stability</strong></td>
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <td><input type="radio" name="affective[emotional_stability]" value="{{ $i }}" {{ ($remark->affective_ratings['emotional_stability'] ?? null) == $i ? 'checked' : '' }}></td>
                                                            @endfor
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Health</strong></td>
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <td><input type="radio" name="affective[health]" value="{{ $i }}" {{ ($remark->affective_ratings['health'] ?? null) == $i ? 'checked' : '' }}></td>
                                                            @endfor
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Speaking/Handwriting</strong></td>
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <td><input type="radio" name="affective[speaking_handwriting]" value="{{ $i }}" {{ ($remark->affective_ratings['speaking_handwriting'] ?? null) == $i ? 'checked' : '' }}></td>
                                                            @endfor
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
                                                            <td><strong>Handwriting</strong></td>
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <td><input type="radio" name="psychomotor[handwriting]" value="{{ $i }}" {{ ($remark->psychomotor_ratings['handwriting'] ?? null) == $i ? 'checked' : '' }}></td>
                                                            @endfor
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Verbal Fluency</strong></td>
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <td><input type="radio" name="psychomotor[verbal_fluency]" value="{{ $i }}" {{ ($remark->psychomotor_ratings['verbal_fluency'] ?? null) == $i ? 'checked' : '' }}></td>
                                                            @endfor
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Sports</strong></td>
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <td><input type="radio" name="psychomotor[sports]" value="{{ $i }}" {{ ($remark->psychomotor_ratings['sports'] ?? null) == $i ? 'checked' : '' }}></td>
                                                            @endfor
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Handling Tools</strong></td>
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <td><input type="radio" name="psychomotor[handling_tools]" value="{{ $i }}" {{ ($remark->psychomotor_ratings['handling_tools'] ?? null) == $i ? 'checked' : '' }}></td>
                                                            @endfor
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Drawing & Painting</strong></td>
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <td><input type="radio" name="psychomotor[drawing_painting]" value="{{ $i }}" {{ ($remark->psychomotor_ratings['drawing_painting'] ?? null) == $i ? 'checked' : '' }}></td>
                                                            @endfor
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Games</strong></td>
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <td><input type="radio" name="psychomotor[games]" value="{{ $i }}" {{ ($remark->psychomotor_ratings['games'] ?? null) == $i ? 'checked' : '' }}></td>
                                                            @endfor
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Musical Skills</strong></td>
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <td><input type="radio" name="psychomotor[musical_skills]" value="{{ $i }}" {{ ($remark->psychomotor_ratings['musical_skills'] ?? null) == $i ? 'checked' : '' }}></td>
                                                            @endfor
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- ══════════════════════════════════════════════════════════ --}}
                                    {{-- REMARKS SECTION — varies by class type (Primary / Secondary) --}}
                                    {{-- ══════════════════════════════════════════════════════════ --}}

                                    <!-- Class Teacher's Remark (always shown) -->
                                    <div class="form-group">
                                        <label><strong>Class Teacher's Remark</strong></label>
                                        <textarea name="teacher_remark"
                                                  class="form-control"
                                                  rows="3"
                                                  placeholder="Enter class teacher's remark…">{{ old('teacher_remark', $remark->teacher_remark) }}</textarea>
                                    </div>

                                    @if($isPrimary)
                                        {{-- PRIMARY: Headmaster's Remark (admin/superadmin only) --}}
                                        @if(in_array(Auth::user()->user_type, [1, 2]))
                                        <div class="form-group">
                                            <label>
                                                <strong>
                                                    <i class="fas fa-user-tie mr-1 text-primary"></i>
                                                    Headmaster's Remark
                                                </strong>
                                                <span class="badge badge-primary ml-1" style="font-size:11px;">Primary</span>
                                            </label>
                                            <textarea name="headmaster_remark"
                                                      class="form-control"
                                                      rows="3"
                                                      placeholder="Enter headmaster's remark…">{{ old('headmaster_remark', $remark->headmaster_remark) }}</textarea>
                                        </div>
                                        @else
                                            {{-- Non-admin: show read-only if already saved --}}
                                            @if(!empty($remark->headmaster_remark))
                                            <div class="form-group">
                                                <label>
                                                    <strong>
                                                        <i class="fas fa-user-tie mr-1 text-primary"></i>
                                                        Headmaster's Remark
                                                    </strong>
                                                </label>
                                                <textarea class="form-control" rows="3" readonly
                                                          style="background:#f8f9fa; cursor:not-allowed;">{{ $remark->headmaster_remark }}</textarea>
                                                <small class="text-muted">Only the Headmaster/Admin can edit this field.</small>
                                            </div>
                                            @endif
                                        @endif

                                    @else
                                        {{-- SECONDARY: Principal's Remark (admin/superadmin only) --}}
                                        @if(in_array(Auth::user()->user_type, [1, 2]))
                                        <div class="form-group">
                                            <label>
                                                <strong>
                                                    <i class="fas fa-user-tie mr-1 text-success"></i>
                                                    Principal's Remark
                                                </strong>
                                                <span class="badge badge-success ml-1" style="font-size:11px;">Secondary</span>
                                            </label>
                                            <textarea name="principal_remark"
                                                      class="form-control"
                                                      rows="3"
                                                      placeholder="Enter principal's remark…">{{ old('principal_remark', $remark->principal_remark) }}</textarea>
                                        </div>
                                        @else
                                            {{-- Non-admin: show read-only if already saved --}}
                                            @if(!empty($remark->principal_remark))
                                            <div class="form-group">
                                                <label>
                                                    <strong>
                                                        <i class="fas fa-user-tie mr-1 text-success"></i>
                                                        Principal's Remark
                                                    </strong>
                                                </label>
                                                <textarea class="form-control" rows="3" readonly
                                                          style="background:#f8f9fa; cursor:not-allowed;">{{ $remark->principal_remark }}</textarea>
                                                <small class="text-muted">Only the Principal/Admin can edit this field.</small>
                                            </div>
                                            @endif
                                        @endif
                                    @endif

                                    {{-- Pass is_primary flag so the controller knows which remark to save --}}
                                    <input type="hidden" name="is_primary" value="{{ $isPrimary ? '1' : '0' }}">

                                    <div class="text-center mt-4">
                                        <button type="submit" class="btn btn-success btn-lg px-5">
                                            <i class="fas fa-save"></i> Save All Ratings & Remarks
                                        </button>
                                        <a href="{{ url()->previous() }}" class="btn btn-secondary btn-lg ml-2">
                                            <i class="fas fa-times"></i> Cancel
                                        </a>
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