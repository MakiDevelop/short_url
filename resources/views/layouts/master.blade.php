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

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
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
        
			<!-- Global site tag (gtag.js) - Google Analytics 網頁串流 -->
			<script async src="https://www.googletagmanager.com/gtag/js?id=G-S9MLXSEFX5"></script>
			<script>
			  window.dataLayer = window.dataLayer || [];
			  function gtag(){dataLayer.push(arguments);}
			  gtag('js', new Date());
			
			  gtag('config', 'G-S9MLXSEFX5');
			</script>
        
			<!-- Global site tag (gtag.js) - Google Analytics 網頁串流 -->
			<script async src="https://www.googletagmanager.com/gtag/js?id=G-33ST4GN5J9"></script>
			<script>
			  window.dataLayer = window.dataLayer || [];
			  function gtag(){dataLayer.push(arguments);}
			  gtag('js', new Date());
			
			  gtag('config', 'G-33ST4GN5J9');
			</script>
            <!-- Global site tag (gtag.js) - Google Analytics 追蹤 ID-->
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
            <!-- Microsoft Clarity Tracking Code -->
            <script type="text/javascript">
				(function(c,l,a,r,i,t,y){
					c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
					t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
					y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
				})(window, document, "clarity", "script", "3zho17qmzz");
			</script>
            <!-- End Microsoft Clarity Tracking Code -->
			<!-- Matomo -->
			<script type="text/javascript">
			  var _paq = window._paq = window._paq || [];
			  /* tracker methods like "setCustomDimension" should be called before "trackPageView" */
			  _paq.push(['trackPageView']);
			  _paq.push(['enableLinkTracking']);
			  (function() {
			    var u="//192.168.11.201:9999/";
			    _paq.push(['setTrackerUrl', u+'matomo.php']);
			    _paq.push(['setSiteId', '2']);
			    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
			    g.type='text/javascript'; g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
			  })();
			</script>
			<!-- End Matomo Code -->
			
        @endif
    </head>
    <body>
        @if (env('APP_ENV') != 'local')
            
        @endif
        <header>
            <!-- Fixed navbar -->
            <nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
                <a class="navbar-brand" href="/">CHIBA短網址</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
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
                    <ul class="navbar-nav ms-auto">
                        @if (Auth::guard('user')->check())
                            <li class="nav-item">
                                <a class="nav-link" href="/api/settings">API</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/logout">登出</a>
                            </li>
                        @else
                            <li class="nav-item">
                                <a class="nav-link" href="/login">登入（Google）</a>
                            </li>
                        @endif
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

        <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.9.0/feather.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.3/Chart.min.js"></script>
        <script src="https://use.fontawesome.com/releases/v5.12.0/js/all.js" data-auto-replace-svg="nest"></script>
        <script src="{{ asset('/js/common.js') }}"></script>
        @yield('js_script')

		<!-- The core Firebase JS SDK is always required and must be listed first -->
		<script src="https://www.gstatic.com/firebasejs/7.19.1/firebase-app.js"></script>
		
		<!-- TODO: Add SDKs for Firebase products that you want to use
		     https://firebase.google.com/docs/web/setup#available-libraries -->
		<script src="https://www.gstatic.com/firebasejs/7.19.1/firebase-analytics.js"></script>
		
		<script>
		  // Your web app's Firebase configuration
		  var firebaseConfig = {
		    apiKey: "AIzaSyBXgrqmHv4gHeaCtw8ZhDw-nFGXdOfuthw",
		    authDomain: "onion-url-shortener-5ab25.firebaseapp.com",
		    databaseURL: "https://onion-url-shortener-5ab25.firebaseio.com",
		    projectId: "onion-url-shortener-5ab25",
		    storageBucket: "onion-url-shortener-5ab25.appspot.com",
		    messagingSenderId: "829735717876",
		    appId: "1:829735717876:web:17564c1207d8bc14a587a1",
		    measurementId: "G-R82Q5ZJSWG"
		  };
		  // Initialize Firebase
		  firebase.initializeApp(firebaseConfig);
		  firebase.analytics();
		</script>
		<!-- Matomo Image Tracker-->
		<img referrerpolicy="no-referrer-when-downgrade" src="http://192.168.11.201:9999/matomo.php?idsite=2&amp;rec=1" style="border:0" alt="" />
		<!-- End Matomo -->
    </body>
</html>
