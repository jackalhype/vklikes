<!DOCTYPE html>
<html lang="en">
<head>
@section('meta')
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">    
    <meta property="og:type" content="website" />
<meta property="og:description" content="@yield('description', 'Description goes here')">
    <title>@yield('title', 'VK Likes')</title>
@show

    <link rel="stylesheet" href="{{ asset('bootstrap/css/bootstrap.min.css') }}">    
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{time()}}">
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('bootstrap/js/bootstrap.min.js') }}"></script>
    {{-- <script src="{{ asset('js/jquery.validate.min.js') }}"></script> --}}
    <script src="{{ asset('js/typeahead.jquery.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.file-input.js') }}"></script>
    <script src="{{ asset('js/main.js') }}?v={{time()}}"></script>

</head>

<body class="main">
@if (env('APP_ENV') === 'production')

@endif

    @yield('content')
</body>
</html>
