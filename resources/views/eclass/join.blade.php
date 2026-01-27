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
                            <div class="card-header d-flex justify-content-between">
                                <h4>{{ $session->title }}</h4>
                                @if(Auth::user()->user_type == 3 && $session->teacher_id == Auth::id())
                                <form action="{{ route('eclass.end', $session->id) }}" method="POST"
                                    style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-sm"
                                        onclick="return confirm('End this session?')">End Session</button>
                                </form>
                                @endif
                            </div>

                            <div class="card-body">
                                <p><strong>Subject:</strong> {{ $session->course?->course_name ?? 'General' }}</p>
                                <p><strong>Class:</strong> {{ $session->schoolClass?->name ?? 'All Classes' }}</p>
                                <p><strong>Start:</strong> {{ $session->start_time->format('d M Y, h:i A') }}</p>
                                <p><strong>Duration:</strong> {{ $session->duration_minutes }} minutes</p>

                                @if($session->description)
                                <p><strong>Description:</strong> {{ $session->description }}</p>
                                @endif

                                <hr>

                                <div id="jitsi-container"
                                    style="width:100%; height:600px; border:1px solid #ddd; border-radius:8px;"></div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        @include('includes.edit_footer')
    </div>
</body>

<!-- Load 8x8 Jitsi Script -->
<script src='https://8x8.vc/{{ env(' JITSI_APP_ID') }}/external_api.js'></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const domain = '8x8.vc';
        const isTeacher = {{ in_array(Auth::user()->user_type, [1, 2, 3]) ? 'true' : 'false' }};
        
        const options = {
            roomName: "{{ env('JITSI_APP_ID') }}/{{ $session->room_name }}",
            width: '100%',
            height: 600,
            parentNode: document.querySelector('#jitsi-container'),
            userInfo: {
                displayName: "{{ Auth::user()->name }}"
            },
            configOverwrite: {
                startWithAudioMuted: !isTeacher,
                startWithVideoMuted: false,
                prejoinPageEnabled: false,  // Already have this
                disableDeepLinking: true,   // ADD THIS
                enableNoAudioDetection: false, // ADD THIS
                enableNoisyMicDetection: false, // ADD THIS
            },
            interfaceConfigOverwrite: {
                TOOLBAR_BUTTONS: [
                    'microphone', 'camera', 'desktop', 'fullscreen',
                    'fodeviceselection', 'hangup', 'profile', 'chat',
                    'settings', 'raisehand', 'videoquality', 'filmstrip', 
                    'tileview'
                ],
                DISABLE_VIDEO_BACKGROUND: true, // ADD THIS
            }
        };
        
        const api = new JitsiMeetExternalAPI(domain, options);
        
        api.addEventListener('videoConferenceJoined', function () {
            console.log('Joined conference successfully');
        });
        
        api.addEventListener('videoConferenceLeft', function () {
            window.location.href = "{{ route('eclass.index') }}";
        });
        
        // ADD ERROR HANDLING
        api.addEventListener('readyToClose', function () {
            console.log('Jitsi ready to close');
        });
    });
</script>