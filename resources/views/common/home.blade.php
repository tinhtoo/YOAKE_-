<!doctype html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <!-- title -->
    <title>@yield('title')</title>

    <!-- Scripts -->
    <script src="{{ asset('js/main.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link rel="stylesheet" href="{{ asset('css/editor.css') }}">
    <link rel="stylesheet" href="{{ asset('css/mainMenu.css') }}">
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/private_page.css') }}">
    <link rel="stylesheet" href="{{ asset('css/search_page.css') }}">
    <link rel="stylesheet" href="{{ asset('css/scroll.css') }}">
    <link rel="stylesheet" href="{{ asset('css/WorkTmMngMenuRoot.css') }}">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <!-- Bootstrap javascript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"
        integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>

    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.3/dist/jquery.validate.js"></script>
    <script src="https://ajax.aspnetcdn.com/ajax/jquery.validate/1.19.2/jquery.validate.min.js"></script>
    <script src="{{ asset('js/loadingoverlay.min.js') }}"></script>
    <script src="{{ asset('js/loadingoverlay_progress.min.js') }}"></script>
    <!-- font-awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
        integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- bootstrap-datepickerを読み込む -->
    <link rel="stylesheet" type="text/css" href="{{ asset('bootstrap-datepicker-1.6.4-dist/css/bootstrap-datepicker.min.css') }}">
    <script type="text/javascript" src="{{ asset('bootstrap-datepicker-1.6.4-dist/js/bootstrap-datepicker.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('bootstrap-datepicker-1.6.4-dist/locales/bootstrap-datepicker.ja.min.js') }}"></script>

    @yield('script')
    <!-- 自動翻訳させないための記述 -->
    <meta http-equiv="Content-Language" content="ja">
    <meta name="google" content="notranslate">
</head>

<body>
    @yield('header')
    @yield('login')
    @yield('main')
    @yield('content_search')
</body>

</html>
