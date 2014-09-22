<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
    <title>Secretstore :: @yield('title')</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="_token" content="{{ csrf_token() }}"/>

    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/jquery-ui.min.js"></script>
    <script type="text/javascript" src="{{ asset('/js/secretstore.js') }}"></script>

    <link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/themes/smoothness/jquery-ui.css" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/css/style.css') }}">
</head>
<body>
    <div id="container">
        <header>{{ Lang::get('secretstore.secretstore') }}</header>

        <div id="main">
            @section('sidebar')
                @include('layouts.sidebar')
            @show

            <div id="content">
                @yield('content')
            </div>
        </div>

        <footer></footer>
    </div>

    @yield('footer')

    <script>window.jQuery || document.write('<script src="{{ asset('/js/jquery.min.js') }} "><\/script>')</script>
</body>
</html>
