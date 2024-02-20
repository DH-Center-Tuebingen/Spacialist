<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="favicon.png" sizes="32x32">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $p['prefs.project-name'] }}</title>

    @vite([
        'resources/js/app.js',
        'resources/sass/app' . $color . '.scss',
    ])
</head>
<body>
    @if($access)
    <div id="app" class="d-flex flex-column"></div>
    @else
    <div class="d-flex flex-column mt-5 align-items-center justify-content-center">
        <h2>
            Open Access not allowed
        </h2>
        <p class="lead alert alert-danger">
            The public feature is not activated. Please contact the project's administrator for access.
        </p>
    </div>
    @endif
</body>
</html>
