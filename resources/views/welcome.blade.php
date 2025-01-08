<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- resources/views/layouts/app.blade.php or public/index.html -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

        <title>Laravel</title>
        @vite('resources/js/app.js')
    </head>
    <body >
        <div id="app"> </div>
    </body>
</html>
