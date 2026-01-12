<!DOCTYPE html>

<html>

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="author" content="">
    <meta name="description" content="">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:site" content="@publisher_handle">

    @yield('head')

    <link href="/pages/css/bootstrap.min.css" rel="stylesheet">
    <link href="/pages/css/blue-theme.css" rel="stylesheet">
    <link href="/pages/css/responsive.css" rel="stylesheet">

    @stack('css')

</head>

<body>


<div id="page" class="page font--jakarta">

    @yield('content')

</div>


<script src="{{ url('theme/libs/jquery/dist/jquery.min.js') }}"></script>
<script src="{{ url('theme/libs/jquery-validation/dist/jquery.validate.min.js') }}"></script>

@stack('scripts')

</body>

</html>
