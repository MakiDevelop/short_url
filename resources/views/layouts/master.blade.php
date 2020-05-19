<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta property="og:title" content="CHIBA短網址"/>
        <meta property="og:description" content="ChibaKuma的縮址服務，完全免費，歡迎大
家多多利用，登入後有個人縮址記錄還有成效追蹤。"/>
        <meta property="og:type" content="website"/>
        <meta property="og:image" content="image/ogimg.png"/>
        <title>CHIBA短網址</title>

        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
        <style>
        .bd-placeholder-img {
            font-size: 1.125rem;
            text-anchor: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        @media (min-width: 768px) {
            .bd-placeholder-img-lg {
                font-size: 3.5rem;
            }
        }
        </style>
        <link href="{{ asset('/css/sticky-footer-navbar.css') }}" rel="stylesheet">
        @yield('css_link')

        @if (env('APP_ENV') != 'local')
            <!-- Global site tag (gtag.js) - Google Analytics -->
            <script async src="https://www.googletagmanager.com/gtag/js?id=UA-162103785-1"></script>
            <script>
                window.dataLayer = window.dataLayer || [];
                function gtag(){dataLayer.push(arguments);}
                gtag('js', new Date());

                gtag('config', 'UA-162103785-1');
            </script>
            <!-- Facebook Pixel Code -->
            <script>
                !function(f,b,e,v,n,t,s)
                {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
                n.callMethod.apply(n,arguments):n.queue.push(arguments)};
                if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
                n.queue=[];t=b.createElement(e);t.async=!0;
                t.src=v;s=b.getElementsByTagName(e)[0];
                s.parentNode.insertBefore(t,s)}(window,document,'script',
                'https://connect.facebook.net/en_US/fbevents.js');
                fbq('init', '2367999386825868'); 
                fbq('track', 'PageView');
            </script>
            <noscript>
                <img height="1" width="1" 
                src="https://www.facebook.com/tr?id=2367999386825868&ev=PageView
                &noscript=1"/>
            </noscript>
            <!-- End Facebook Pixel Code -->
            <!-- Matomo -->
            <script type="text/javascript">
                var _paq = window._paq || [];
                /* tracker methods like "setCustomDimension" should be called before "trackPageView" */
                _paq.push(['trackPageView']);
                _paq.push(['enableLinkTracking']);
                (function() {
                    var u="//matomo.chiba.tw/";
                    _paq.push(['setTrackerUrl', u+'matomo.php']);
                    _paq.push(['setSiteId', '2']);
                    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
                    g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
                })();
            </script>
            <noscript><p><img src="//matomo.chiba.tw/matomo.php?idsite=2&amp;rec=1" style="border:0;" alt="" /></p></noscript>
            <!-- End Matomo Code -->
        @endif
    </head>
    <body>
        @if (env('APP_ENV') != 'local')
            <!-- Matomo Image Tracker-->
            <img src="http://matomo.chiba.tw/matomo.php?idsite=2&amp;rec=1" style="border:0" alt="" />
            <!-- End Matomo -->
        @endif
        <header>
            <!-- Fixed navbar -->
            <nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
                <a class="navbar-brand" href="/">CHIBA短網址</a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarCollapse">
                    {{-- <ul class="navbar-nav mr-auto"> --}}
                        {{-- <li class="nav-item active">
                            <a class="nav-link" href="#">Home <span class="sr-only">(current)</span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Link</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link disabled" href="#" tabindex="-1" aria-disabled="true">Disabled</a>
                        </li> --}}
                    {{-- </ul> --}}
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item">
                            @if (Auth::guard('user')->check())
                                <a class="nav-link" href="/logout">登出</a>
                            @else 
                                <a class="nav-link" href="/login">登入（Google）</a>
                            @endif
                        </li>
                    </ul>
                </div>
            </nav>
        </header>

        <main role="main" class="flex-shrink-0">
            <div class="container">
                @yield('content')
            </div>
        </main>

        <footer class="footer mt-auto py-3">
            <div class="container">
              {{-- <span class="text-muted">Place sticky footer content here.</span> --}}
            </div>
        </footer>

        <script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.9.0/feather.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.3/Chart.min.js"></script>
        <script src="https://use.fontawesome.com/releases/v5.12.0/js/all.js" data-auto-replace-svg="nest"></script>
        <script src="{{ asset('/js/common.js') }}"></script>
        @yield('js_script')
    </body>
</html>
