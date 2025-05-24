<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    @class([
            'fi min-h-screen',
            'dark' => filament()->hasDarkModeForced(),
        ])
>
<head>
    <meta charset="utf-8">
    <meta name="application-name" content="{{ config('app.name') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- <title>Sistem Try Out Online</title> -->
    <title>
        {{ isset($title) && filled($title = strip_tags($title)) ? "{$title} - " : '' }}
        {{ filament()->getBrandName() }}
    </title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    @if ($favicon = filament()->getFavicon())
        <link rel="icon" href="{{ $favicon }}" />
    @endif
    
    {{ \Filament\Support\Facades\FilamentView::renderHook('panels::styles.before') }}

    <style>
        body {
            padding-top: 20px;
        }
        .question-card {
            margin-bottom: 20px;
        }
        .question-navigation {
            position: sticky;
            top: 20px;
        }
        .btn-group-flex {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }
        .countdown-timer {
            text-align: center;
            font-size: 1.5em;
            margin-top: 20px;
        }

        [x-cloak=''],
        [x-cloak='x-cloak'],
        [x-cloak='1'] {
            display: none !important;
        }

        @media (max-width: 1023px) {
            [x-cloak='-lg'] {
                display: none !important;
            }
        }

        @media (min-width: 1024px) {
            [x-cloak='lg'] {
                display: none !important;
            }
        }
    </style>
    @filamentStyles
    {{ filament()->getTheme()->getHtml() }}
    {{ filament()->getFontHtml() }}
    <style>
        :root {
            --font-family: {!! filament()->getFontFamily() !!};
            --sidebar-width: {{ filament()->getSidebarWidth() }};
            --collapsed-sidebar-width: {{ filament()->getCollapsedSidebarWidth() }};
        }
    </style>
    @if (! filament()->hasDarkMode())
        <script>
            localStorage.setItem('theme', 'light')
        </script>
    @elseif (filament()->hasDarkModeForced())
        <script>
            localStorage.setItem('theme', 'dark')
        </script>
    @else
        <script>
            const theme = localStorage.getItem('theme') ?? 'system'

            if (
                theme === 'dark' ||
                (theme === 'system' &&
                    window.matchMedia('(prefers-color-scheme: dark)')
                        .matches)
            ) {
                document.documentElement.classList.add('dark')
            }
        </script>
    @endif
    @vite('resources/css/app.css')
    @vite('resources/css/career.css')
    @vite('resources/css/career-job-post.css')
</head>

<body class="fi-body min-h-screen bg-gray-50 font-normal text-gray-950 antialiased dark:bg-gray-950 dark:text-white">
{{ $slot }}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.min.js" integrity="sha384-RuyvpeZCxMJCqVUGFI0Do1mQrods/hhxYlcVfGPOfQtPJh0JCw12tUAZ/Mv10S7D" crossorigin="anonymous"></script>

@livewire('notifications')
@filamentScripts(withCore: true)
@vite('resources/js/app.js')
@stack('scripts')
</body>
</html>
