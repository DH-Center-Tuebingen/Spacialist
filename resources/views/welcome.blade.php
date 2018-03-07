@extends('layouts.app')

@section('content')
    <div class="d-flex flex-row align-items-center h-100">
        <div class="col-md-6 offset-md-3 text-center">
            <img src="img/logo.png" width="200px;" />
            <h1>Welcome to {{ $preferences['prefs.project-name']['value'] }}!</h1>
            <p>
                This Instance is maintained by <span class="font-italic">{{ $preferences['prefs.project-maintainer']['value']->name }}</span> (<a href="mailto:{{ $preferences['prefs.project-maintainer']['value']->email }}" target="_blank">{{ $preferences['prefs.project-maintainer']['value']->email }}</a>)
            </p>

            <h2 class="mt-5"><i class="far fa-fa fa-clipboard text-info"></i> What is {{ $preferences['prefs.project-name']['value'] }} about?</h2>
            <p>
                {{ $preferences['prefs.project-maintainer']['value']->description }}
            </p>

            <h2 class="mt-5"><i class="fas fa-fa fa-shield-alt text-info"></i> External Access</h2>
            <div>
                @if ($preferences['prefs.project-maintainer']['value']->public)
                <p>
                    <i class="fas fa-fw fa-check-circle text-success"></i> This instance allows public access. Please note, that this account may be restricted and thus limited in actions, viewing data and editing.
                </p>
                <a href="{{ route('home') }}" class="btn btn-outline-success">Access</a>
                @else
                <p>
                    <i class="fas fa-fw fa-exclamation-circle text-warning"></i> This instance is not meant for public access. Please ask the maintainer for an account.
                </p>
                <a href="{{ route('login') }}" class="btn btn-outline-primary">Login</a>
                @endif
            </div>
        </div>
    </div>
@endsection
