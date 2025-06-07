<nav class="navbar navbar-expand py-3">
    <div class="container-fluid d-flex flex-wrap align-items-center justify-content-between">
        <a class="navbar-brand" href="#">Navbar</a>
        <ul class="navbar-nav d-flex flex-wrap align-items-center">

            {{-- @if (session()->has('username'))
            <!-- User sudah login -->
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                    <img src="{{ asset('assets/images/default-profile.png') }}" alt="Profile" class="rounded-circle profile-photo">
            <span class="ms-2">Hi, {{ session('username') }}</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <div class="dropdown-item">Static User</div>
                </li>
                <li><a class="dropdown-item" href="/profile">Profile</a></li>
                <li>
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="dropdown-item">Logout</button>
                    </form>
                </li>
            </ul>
            </li>
            @else
            <!-- Belum login -->
            <li class="nav-item">
                <a href="{{ route('show.login') }}" class="btn btn-custom btn-gray">Login</a>
            </li>
            @endif --}}


            <li class="nav-item">
                <button class="btn btn-custom btn-purple" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight">History</button>
            </li>
        </ul>
    </div>

</nav>
<!-- Offcanvas History -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasRightLabel">History</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body" id="historyContainer">
        <!-- History items akan ditambahkan secara dinamis oleh JavaScript -->
    </div>
</div>
