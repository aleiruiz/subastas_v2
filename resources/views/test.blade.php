<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>

    </style>
</head>
<body>
<div id="app">
    <div class="timer">
    <Timer
        starttime="Nov 5, 2018 15:37:25"
        endtime="Nov 8, 2020 16:37:25"
        trans='{
         "day":"D",
         "hours":"H",
         "minutes":"M",
         "seconds":"S",
         "expired":"Event has been expired.",
         "running":"Till the end of event.",
         "upcoming":"Till start of event.",
         "status": {
            "expired":"Expired",
            "running":"Running",
            "upcoming":"Future"
           }}'
    ></Timer>
    </div>
</div>
<script src="{{ asset('js/app.js') }}"></script>
<script>
    new Vue({
        el: "#app",
    });
</script>
</body>
</html>
