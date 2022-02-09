@extends('layouts.laravel')

@section('content')
    <div class="d-flex flex-row align-items-center h-100">
        <div class="col-2 h-100 border-end p-3">
            <a href="." class="d-flex align-items-center mb-3 gap-2 link-dark text-decoration-none">
                <img src="img/logo.png" width="32px;" />
                <span class="fs-4">Home</span>
            </a>
            <hr/>
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item">
                    <a class="nav-link link-dark {{ $site == 'start' ? 'active' : '' }}" href="?s=start">
                        Start
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link link-dark {{ $site == 'about' ? 'active' : '' }}" href="?s=about">
                        About
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link link-dark {{ $site == 'access' ? 'active' : '' }}" href="?s=access">
                        Access
                    </a>
                </li>
            </ul>
        </div>
        <div class="col-10 h-100 px-5 py-3 text-center">
            @if ($site == 'start')

            <div>
                <img src="img/logo.png" width="200px;" />
                <h1>Welcome to {{ $p['prefs.project-name'] }}!</h1>

                <p class="lead d-inline-block p-3 my-5 bg-primary bg-opacity-10 rounded-3">
                    This project is realized using <a href="https://uni-tuebingen.de/forschung/forschungsinfrastruktur/escience-center/spacialist-1/" target="_blank">Spacialist</a> as foundation.
                    <br/>
                    On this welcome page you can find some more information about the content of the project, the maintainers and how you can gain access.
                </p>
                <p>
                    For more information about the project and it's content, please head over to <a href="?s=about">About section</a>.
                    <br/>
                    <br/>
                    If you want to start using it, check the <a href="?s=access">Access page</a>, whether it is available as <span class="fst-italic">Open Access</span> or if you need an account.
                </p>
            </div>

            @elseif ($site == 'about')

            <div class="d-flex flex-column justify-content-between h-100">
                <div>
                    <h2 class="display-5"><i class="far fa-fw fa-clipboard text-info"></i> What is this about?</h2>
                    <div class="mt-5 p-3 bg-primary bg-opacity-10 rounded-3 d-inline-block text-start rendered-markdown">
                        {!! Illuminate\Support\Str::markdown($p['prefs.project-maintainer']->description) !!}
                    </div>
                </div>
    
                <div>
                    <span class="text-muted small">
                        Maintained by <span class="font-italic">{{ $p['prefs.project-maintainer']->name }}</span> (<a href="mailto:{{ $p['prefs.project-maintainer']->email }}" target="_blank">{{ $p['prefs.project-maintainer']->email }}</a>)
                    </span>
                </div>
            </div>

            @elseif ($site == 'access')

            <h2 class="">
                <i class="fas fa-fw fa-shield-alt text-info"></i>
                External Access
            </h2>
            <div>
                @if ($p['prefs.project-maintainer']->public)
                <p>
                    <i class="fas fa-fw fa-check-circle text-success"></i> This instance allows public access. Please note, that this account may be restricted and thus limited in actions, viewing data and editing.
                </p>
                <a href="{{ route('home') }}" class="btn btn-outline-success">Access</a>
                @else
                <p>
                    <i class="fas fa-fw fa-exclamation-circle text-warning"></i> This instance is not meant for public access. Please ask the maintainer for an account.
                </p>
                <a href="{{ route('home') }}" class="btn btn-outline-primary">Login</a>
                @endif
            </div>

            @endif
        </div>
    </div>
@endsection
