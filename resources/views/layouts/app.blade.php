<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'QuestBooking' }}</title>

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Общие стили --}}
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        footer {
            margin-top: auto;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

    {{-- Верхнее меню --}}
    @include('layouts.navigation')

    {{-- Контент страницы --}}
    <main class="flex-grow-1 py-5">
        <div class="container">
            @yield('content')
        </div>
    </main>

    {{-- Подвал --}}
    <footer class="bg-dark text-light text-center py-3">
        <div class="container">
            © {{ date('Y') }} QuestBooking · Учебный проект
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
