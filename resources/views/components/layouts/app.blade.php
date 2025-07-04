<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Page Title' }}</title>
    <meta content='width=device-width, initial-scale=1.0, shrink-to-fit=no' name='viewport' />
    <link rel="icon" href="{{ asset('assets/img/lai/lai.ico') }}" type="image/x-icon" />

    <!-- Fonts and icons -->
    <script src="{{ asset('assets/js/plugin/webfont/webfont.min.js') }}" data-navigate-once></script>
    <script>
        WebFont.load({
            google: {
                "families": ["Lato:300,400,700,900"]
            },
            custom: {
                "families": ["Flaticon", "Font Awesome 5 Solid", "Font Awesome 5 Regular", "Font Awesome 5 Brands",
                    "simple-line-icons"
                ],
                urls: ['{{ asset('assets/css/fonts.min.css') }}']
            },
            active: function() {
                sessionStorage.fonts = true;
            }
        });
    </script>

    <!-- CSS Files -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/atlantis.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/choices.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
    {{-- <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css"> --}}

    <!-- CSS Just for demo purpose, don't include it in your project -->
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}">

    @livewireStyles
</head>

<body>
    <div class="wrapper">
        <div class="main-header">
            <!-- Logo Header -->
            <div class="logo-header" style="background-color: #b3e9ff;">

                <a href="{{ route('dashboard') }}" class="logo">
                    <img class="w-75" src="{{ asset('assets/img/lai/horizontal-logo.png') }}" alt="navbar brand"
                        class="navbar-brand">
                </a>
                <button class="navbar-toggler sidenav-toggler ml-auto" type="button" data-toggle="collapse"
                    data-target="collapse" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon">
                        <i class="icon-menu"></i>
                    </span>
                </button>
                <button class="topbar-toggler more"><i class="icon-options-vertical"></i></button>
                <div class="nav-toggle">
                    <button class="btn btn-toggle toggle-sidebar">
                        <i class="icon-menu"></i>
                    </button>
                </div>
            </div>
            <!-- End Logo Header -->

            <!-- Navbar Header -->
            <livewire:navbar />
            <!-- End Navbar -->
        </div>

        <!-- Sidebar -->
        <livewire:sidebar />
        <!-- End Sidebar -->

        <div class="main-panel">
            <div class="content">
                <div class="panel-header bg-primary-gradient">
                    <div class="page-inner py-5">
                        <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row">
                            <div>
                                <h2 class="text-white pb-2 fw-bold">{{ $title ?? 'Page Title' }}</h2>
                                <h5 class="text-white op-7 mb-2">{{ $subTitle ?? '' }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="page-inner mt--5">
                    {{ $slot }}
                </div>
            </div>
        </div>

    </div>
    <!--   Core JS Files   -->
    <script src="{{ asset('assets/js/core/jquery.3.2.1.min.js') }}" data-navigate-once></script>
    <script src="{{ asset('assets/js/core/popper.min.js') }}" data-navigate-once></script>
    <script src="{{ asset('assets/js/core/bootstrap.min.js') }}" data-navigate-once></script>

    <!-- jQuery UI -->
    <script src="{{ asset('assets/js/plugin/jquery-ui-1.12.1.custom/jquery-ui.min.js') }}" data-navigate-once></script>
    <script src="{{ asset('assets/js/plugin/jquery-ui-touch-punch/jquery.ui.touch-punch.min.js') }}" data-navigate-once>
    </script>

    <!-- jQuery Scrollbar -->
    <script src="{{ asset('assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js') }}" data-navigate-once></script>

    <!-- Chart JS -->
    <script src="{{ asset('assets/js/plugin/chart.js/chart.min.js') }}" data-navigate-once></script>

    <!-- jQuery Sparkline -->
    <script src="{{ asset('assets/js/plugin/jquery.sparkline/jquery.sparkline.min.js') }}" data-navigate-once></script>

    <!-- Chart Circle -->
    <script src="{{ asset('assets/js/plugin/chart-circle/circles.min.js') }}" data-navigate-once></script>

    <!-- Datatables -->
    <script src="{{ asset('assets/js/plugin/datatables/datatables.min.js') }}"></script>

    <!-- Bootstrap Notify -->
    <script src="{{ asset('assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js') }}" data-navigate-once></script>

    <!-- jQuery Vector Maps -->
    <script src="{{ asset('assets/js/plugin/jqvmap/jquery.vmap.min.js') }}" data-navigate-once></script>
    <script src="{{ asset('assets/js/plugin/jqvmap/maps/jquery.vmap.world.js') }}" data-navigate-once></script>

    <!-- Sweet Alert -->
    <script src="{{ asset('assets/js/plugin/sweetalert/sweetalert.min.js') }}" data-navigate-once></script>

    {{-- Alpine JS --}}
    {{-- <script src="{{ asset('assets/js/alpine.min.js') }}" defer></script> --}}

    <!-- Atlantis JS -->
    <script src="{{ asset('assets/js/atlantis.min.js') }}" data-navigate-once></script>
    <script src="{{ asset('assets/js/choices.min.js') }}" data-navigate-once></script>

    {{-- Bootstrap Select --}}
    <!-- Put this at the end of your <body> section -->
    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script> --}}


    @livewireScripts
    @stack('scripts')
</body>

</html>
