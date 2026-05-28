<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|manrope:500,600,700,800|figtree:400,500,600&display=swap" rel="stylesheet" />

        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

        <!-- Theme bootstrap (anti-FOUC) — must run before Vue mounts.
             For CRM routes we force the light theme so .crm-theme stays unchanged. -->
        <script>
            (function () {
                try {
                    var path = window.location.pathname || '';
                    var isCrm = path.indexOf('/crm') === 0
                        || path.indexOf('/dashboard') === 0
                        || path.indexOf('/profile') === 0
                        || path.indexOf('/login') === 0
                        || path.indexOf('/register') === 0
                        || path.indexOf('/forgot-password') === 0
                        || path.indexOf('/reset-password') === 0
                        || path.indexOf('/two-factor') === 0
                        || path.indexOf('/user/') === 0
                        || path.indexOf('/api/') === 0;

                    var stored = null;
                    try { stored = window.localStorage.getItem('theme'); } catch (e) {}

                    var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                    var resolved = stored === 'dark' || stored === 'light'
                        ? stored
                        : (prefersDark ? 'dark' : 'dark'); // public default: dark

                    var html = document.documentElement;
                    if (isCrm) {
                        html.classList.remove('dark');
                        html.classList.add('light');
                        html.setAttribute('data-theme', 'light');
                        html.setAttribute('data-area', 'crm');
                    } else {
                        html.classList.remove('light');
                        html.classList.toggle('dark', resolved === 'dark');
                        html.setAttribute('data-theme', resolved);
                        html.setAttribute('data-area', 'public');
                    }
                } catch (err) { /* fail silent */ }
            })();
        </script>

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
        <!-- Yandex.Metrika counter -->
        <script type="text/javascript">
            window.yandexMetrikaCounterId = 108375589;
            window.dataLayer = window.dataLayer || [];

            (function(m,e,t,r,i,k,a){
                m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
                m[i].l=1*new Date();
                for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
                k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)
            })(window, document, 'script', 'https://mc.yandex.ru/metrika/tag.js?id=108375589', 'ym');

            ym(108375589, 'init', {
                defer: true,
                ssr: true,
                webvisor: true,
                clickmap: true,
                ecommerce: 'dataLayer',
                accurateTrackBounce: true,
                trackLinks: true
            });
        </script>
        <!-- /Yandex.Metrika counter -->
    </head>
    <body class="font-sans antialiased">
        <noscript>
            <div><img src="https://mc.yandex.ru/watch/108375589" style="position:absolute; left:-9999px;" alt="" /></div>
        </noscript>
        @inertia
    </body>
</html>
