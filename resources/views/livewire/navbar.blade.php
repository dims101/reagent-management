<nav class="navbar navbar-header navbar-expand-lg" data-background-color="white">
    <div class="container-fluid">
        {{-- Search Bar --}}
        <div class="collapse" id="search-nav">
            <form class="navbar-left navbar-form nav-search mr-md-3">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <button type="submit" class="btn btn-search pr-1">
                            <i class="fa fa-search search-icon"></i>
                        </button>
                    </div>
                    <input type="text" placeholder="Search ..." class="form-control">
                </div>
            </form>
        </div>
        {{-- End Search Bar --}}
        <ul class="navbar-nav topbar-nav ml-md-auto align-items-center border border-primary px-3 mr-2">
            {{-- <li class="nav-item toggle-nav-search hidden-caret">
                <a class="nav-link" data-toggle="collapse" href="#search-nav" role="button" aria-expanded="false"
                    aria-controls="search-nav">
                    tes
                    <i class="fa fa-search"></i>
                </a>
            </li> --}}
            <li class="nav-item dropdown">
                <div class="avatar-sm">
                    <img src="{{ asset('assets/img/profile.png') }}" alt="..." class="avatar-img rounded-circle">
                </div>
            </li>
            <li class="nav-item dropdown hidden-caret">
                <a style="text-decoration: none;" class="dropdown-toggle" data-toggle="dropdown" href="#"
                    aria-expanded="false">
                    {{ auth()->user()->name ?? '-' }} ({{ auth()->user()->nup ?? '-' }})
                </a>
                {{-- Profile drop down --}}
                <ul class="dropdown-menu dropdown-user animated fadeIn">
                    <div class="dropdown-user-scroll scrollbar-outer">
                        <li>
                            <div class="user-box">
                                <div class="avatar-lg">
                                    <img src="{{ asset('assets/img/profile.png') }}" alt="image profile"
                                        class="avatar-img rounded">
                                </div>
                                <div class="u-text">
                                    <h4>{{ auth()->user()->name ?? '-' }}</h4>
                                    <p class="text-muted">{{ auth()->user()->email ?? '-' }}</p>
                                    {{-- <a href="#" wire:click.prevent="openChangePasswordModal"
                                        class="btn btn-xs btn-primary btn-sm">Change Password</a>
                                </div> --}}
                                </div>
                        </li>
                        <li>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="#" onclick="logoutWithConfirmation()">
                                {{ __('Logout') }}
                            </a>
                            {{-- <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form> --}}

                        </li>
                    </div>
                </ul>
                {{-- End Profile drop down --}}
            </li>
        </ul>
        <ul class="navbar-nav">
            <li class="nav-item dropdown hidden-caret">
                <!-- Tombol Home -->
                <a href="{{ route('dashboard') }}"
                    class="btn btn-primary btn-sm ml-2 d-inline-block align-middle rounded-circle" title="Home">
                    <i class="fa fa-home"></i>
                </a>
                <!-- Tombol Logout -->
                <a href="#" class="btn btn-danger btn-sm ml-2 d-inline-block align-middle rounded-circle"
                    title="Logout" onclick="logoutWithConfirmation()">
                    <i class="fa fa-power-off"></i>
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </li>
        </ul>
    </div>
</nav>
@push('scripts')
    <script>
        function logoutWithConfirmation() {
            swal({
                title: 'Are you sure?',
                text: "You will be logged out.",
                icon: 'warning',
                buttons: {
                    cancel: {
                        text: 'Cancel',
                        visible: true,
                        className: 'btn btn-danger btn-pill'
                    },
                    confirm: {
                        text: 'Yes, logout!',
                        className: 'btn btn-success btn-pill'
                    }
                }
            }).then((willLogout) => {
                if (willLogout) {
                    document.getElementById('logout-form').submit();
                }
            });
        }
    </script>
@endpush
