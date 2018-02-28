@extends('layouts.app')

@section('content')
    <div class="d-flex flex-row align-items-center h-100">
        <div class="col-md-6 offset-md-3 text-center">
            <img src="img/logo.png" width="200px;" />
            <h1>Welcome to {{ config('app.name', 'Spacialist') }}!</h1>
            <p>
                This Instance is maintained by <i>Spacialist Developers</i> (<a href="mailto:spacialist@escience.uni-tuebingen.de" target="_blank">spacialist@escience.uni-tuebingen.de</a>)
            </p>

            <h2 class="mt-5"><i class="far fa-fa fa-clipboard text-info"></i> What is {{ config('app.name', 'Spacialist') }} about?</h2>
            <p>
                This instance is a simple testing instance. Everyone can play around and test it. But, manipulation is restricted to Spacialist staff.
            </p>

            <h2 class="mt-5"><i class="fas fa-fa fa-shield-alt text-info"></i> External Access</h2>
            <div>
                <p>
                    <i class="fas fa-fw fa-exclamation-circle text-warning"></i> This instance is not meant for public access. Please ask the maintainer for an account.
                </p>
                <a href="{{ route('login') }}" class="btn btn-outline-primary">Login</a>
            </div>
            <div>
                <p>
                    <i class="fas fa-fw fa-check-circle text-success"></i> This instance allows public access. Please note, that this account may be restricted and thus limited in actions, viewing data and editing.
                </p>
                <a href="{{ route('home') }}" class="btn btn-outline-success">Access</a>
            </div>
        </div>
    </div>
@endsection
