<div class="sidebar sidebar-style-2">
    <div class="sidebar-wrapper scrollbar scrollbar-inner">
        <div class="sidebar-content">
            <div class="user">
                <div class="avatar-sm float-left mr-2">
                    <img src="../assets/img/profile.jpg" alt="..." class="avatar-img rounded-circle">
                </div>
                <div class="info">
                    <a data-toggle="collapse" href="#collapseExample" aria-expanded="true">
                        <span>
                            Hizrian
                            <span class="user-level">Administrator</span>
                            <span class="caret"></span>
                        </span>
                    </a>
                    <div class="clearfix"></div>

                    <div class="collapse in" id="collapseExample">
                        <ul class="nav">
                            <li>
                                <a href="#">
                                    <span class="link-collapse">Change Password</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <ul class="nav nav-primary">
                <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}" wire:navigate>
                        <i class="fas fa-home"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                <li class="nav-item {{ request()->routeIs('create-stock') ? 'active' : '' }}">
                    <a href="{{ route('create-stock') }}" wire:navigate>
                        <i class="fas fa-layer-group"></i>
                        <p>Input Stock</p>
                    </a>
                </li>
                <li class="nav-item {{ request()->routeIs('self-stock') ? 'active' : '' }}">
                    <a href="{{ route('self-stock') }}" wire:navigate>
                        <i class="fas fa-th-list"></i>
                        <p>Stock On Hand</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#">
                        <i class="fas fa-pen-square"></i>
                        <p>Stock Of Others</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#">
                        <i class="fas fa-table"></i>
                        <p>Approval List</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#">
                        <i class="fas fa-map-marker-alt"></i>
                        <p>Reject List</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#">
                        <i class="far fa-chart-bar"></i>
                        <p>History List</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#">
                        <i class="fas fa-bars"></i>
                        <p>Assign Ticket Reagent</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#">
                        <i class="fas fa-bars"></i>
                        <p>Create Ticket Reagent</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#">
                        <i class="fas fa-bars"></i>
                        <p>Ticket Reagent List</p>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
