<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
    <title>Secretstore :: @yield('title')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" type="text/css" href="{{ asset('/css/style.css') }}">
	<style>
	</style>
</head>
<body>
    <div id="container">
        <div id="header"></div>

        <div id="main">
            @section('sidebar')
                @include('layouts.sidebar')
            @show

            <div id="content">
                @yield('content')
            </div>
        </div>

        <div id="footer"></div>
    </div>
</body>
</html>
