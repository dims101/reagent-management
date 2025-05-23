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
        <ul class="navbar-nav topbar-nav ml-md-auto align-items-center border border-primary px-2">
            <li class="nav-item toggle-nav-search hidden-caret">
                <a class="nav-link" data-toggle="collapse" href="#search-nav" role="button" aria-expanded="false"
                    aria-controls="search-nav">
                    <i class="fa fa-search"></i>
                </a>
            </li>
            <li class="nav-item dropdown">
                <div class="avatar-sm">
                    <img src="../assets/img/profile.jpg" alt="..." class="avatar-img rounded-circle">
                </div>
            </li>
            <li class="nav-item dropdown hidden-caret">
                <a style="text-decoration: none;" class="dropdown-toggle" data-toggle="dropdown" href="#"
                    aria-expanded="false">
                    {{-- <button type="button" class="btn btn-outline-primary">Yudi Mulyadi</button> --}}
                    Yudi Mulyadi (LAI0011013)
                </a>
                {{-- Profile drop down --}}
                <ul class="dropdown-menu dropdown-user animated fadeIn">
                    <div class="dropdown-user-scroll scrollbar-outer">
                        <li>
                            <div class="user-box">
                                <div class="avatar-lg"><img src="../assets/img/profile.jpg" alt="image profile"
                                        class="avatar-img rounded"></div>
                                <div class="u-text">
                                    <h4>Hizrian</h4>
                                    <p class="text-muted">hello@example.com</p><a href="profile.html"
                                        class="btn btn-xs btn-primary btn-sm">View Profile</a>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="#">Account Setting</a>
                            <a class="dropdown-item" href="#">Logout</a>
                        </li>
                    </div>
                </ul>
                {{-- End Profile drop down --}}
            </li>
        </ul>
    </div>
</nav>
