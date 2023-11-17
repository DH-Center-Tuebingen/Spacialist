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

    @php
        $color = $p['prefs.color'] ?? '';
    @endphp

    @vite([
            'resources/js/app.js',
            'resources/sass/app' . $color . '.scss',
        ])


        @foreach($plugins as $plugin)
            <script src="storage/plugins/{!! sp_slug($plugin->name) !!}-{!! $plugin->uuid !!}.js" defer> 
            </script>
        @endforeach
</head>
<body>
    <div id="app" class="d-flex flex-column"></div>
</body>
</html>
