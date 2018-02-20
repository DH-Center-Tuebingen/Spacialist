<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}" sizes="32x32">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Spacialist') }}</title>

    <!-- Styles -->
    <link href="{{ asset('css/vue-multiselect.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/bootstrap-datepicker.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-dark bg-dark navbar-static-top navbar-expand-lg">
            <!-- Branding Image -->
            <a class="navbar-brand" href="{{ url('/home') }}">
                <img src="{{ asset('favicon.png') }}" class="logo" alt="spacialist logo" />
                {{ config('app.name', 'Spacialist') }}
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <!-- Left Side Of Navbar -->
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="">
                            Built with <i class="fab fa-fw fa-laravel"></i> & <i class="fab fa-fw fa-vuejs"></i>!
                        </a>
                    </li>
                    <li class="nav-item">
                        <form class="form-inline mr-auto">
                            <div class="form-group">
                                <div class="input-group">
                                    <input class="form-control global-search" type="text" id="global-search" />
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            <i class="fas fa-fw fa-search" aria-hidden="true"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </li>
                </ul>

                <!-- Right Side Of Navbar -->
                <ul class="nav navbar-nav navbar-right">
                    <li class="nav-item">
                        <a class="nav-link" target="_blank" href="https://github.com/eScienceCenter/Spacialist/wiki/User-manual">
                            <i class="far fa-fw fa-question-circle"></i>
                        </a>
                    </li>
                    <!-- Authentication Links -->
                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">
                                Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">
                                Register
                            </a>
                        </li>
                    @else
                        <li class="nav-item dropdown">
                            <a href="#" class="nav-link dropdown-toggle" id="tools-navbar" data-toggle="dropdown" role="button" aria-expanded="false" aria-haspopup="true">
                                <i class="fas fa-fw fa-cogs"></i> Tools
                            </a>
                            <div class="dropdown-menu" aria-labelledby="tools-navbar">
                                <a class="dropdown-item" href="{{ route('gis') }}">
                                    <i class="fas fa-fw fa-globe"></i> GIS
                                </a>
                                <a class="dropdown-item" href="{{ route('bibliography') }}">
                                    <i class="fas fa-fw fa-book"></i> Bibliography
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="{{ route('analysis') }}">
                                    <i class="far fa-fw fa-chart-bar"></i> Data Analysis <sup>(BETA)</sup>
                                </a>
                                <div class="dropdown-divider"></div>
                                <h6 class="dropdown-header">
                                    External Tools <sup class="fas fa-fw fa-sm fa-fw fa-external-link-alt"></sup>
                                </h6>
                                <a class="dropdown-item" href="">
                                    <i class="fas fa-fw fa-paw"></i> ThesauRex
                                </a>
                                <a class="dropdown-item" href="">
                                    <i class="fas fa-fw fa-chart-bar"></i> dbWebGen
                                </a>
                            </div>
                        </li>
                        <li class="nav-item dropdown">
                            <a href="#" class="nav-link dropdown-toggle" id="settings-dropdown" data-toggle="dropdown" role="button" aria-expanded="false" aria-haspopup="true">
                                <i class="fas fa-fw fa-sliders-h"></i> Settings <span class="caret"></span>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="settings-dropdown">
                                <a class="dropdown-item" href="{{ route('users') }}">
                                    <i class="fas fa-fw fa-users"></i> User Management
                                </a>
                                <a class="dropdown-item" href="{{ route('roles') }}">
                                    <i class="fas fa-fw fa-shield-alt"></i> Role Management
                                </a>
                                <a class="dropdown-item" href="{{ route('dme') }}">
                                    <i class="fas fa-fw fa-sitemap"></i> Data Model Editor
                                </a>
                                <a class="dropdown-item" href="{{ route('layer') }}">
                                    <i class="fas fa-fw fa-sticky-note"></i> Layer Editor
                                </a>
                                <a class="dropdown-item" href="{{ route('prefs') }}">
                                    <i class="fas fa-fw fa-cog"></i> System Preferences
                                </a>
                                <a class="dropdown-item" href="">
                                    <i class="fas fa-fw fa-pencil-alt"></i> Toggle Edit Mode
                                </a>
                                <a class="dropdown-item" href="">
                                    <i class="fas fa-fw fa-info-circle"></i> About
                                </a>
                            </div>
                        </li>
                        <li class="nav-item dropdown">
                            <a href="#" class="nav-link dropdown-toggle" id="user-dropdown" data-toggle="dropdown" role="button" aria-expanded="false" aria-haspopup="true">
                                <i class="fas fa-fw fa-user"></i> {{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="user-dropdown">
                                <a class="dropdown-item" href="{{ route('userprefs', ['id' => Auth::user()->id]) }}">
                                    <i class="fas fa-fw fa-cog"></i> Preferences
                                </a>
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                    onclick="event.preventDefault();
                                             document.getElementById('logout-form').submit();">
                                    <i class="fas fa-fw fa-sign-out-alt"></i> Logout
                                </a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                    {{ csrf_field() }}
                                </form>
                            </ul>
                        </li>
                    @endguest
                </ul>
            </div>
        </nav>
        <div class="container-fluid">
            <div class="row mt-3">
                <div class="col-md-12">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        FontAwesomeConfig = { searchPseudoElements: true };
    </script>
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>
</body>
</html>
