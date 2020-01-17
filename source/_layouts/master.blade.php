<!DOCTYPE html>
<html lang="{{ $page->lang ?? 'en' }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <link rel="stylesheet" href="{{ mix('css/main.css', 'assets/build') }}">

  <title>{{ $page->title ? $page->title . ' | The PHP Website' : 'The PHP Website' }}</title>

  @include('_layouts.partials.meta')

  <!-- Global site tag (gtag.js) - Google Analytics -->
  <script async src="https://www.googletagmanager.com/gtag/js?id={{ $page->get('gaId')[$page->lang ?? 'en'] }}"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', '{{ $page->get('gaId')[$page->lang ?? 'en'] }}');
  </script>
</head>
<body class="yue">
  <header>
    @if($page->get('createdAt'))
      <time datetime="{{ date('Y-m-d', $page->get('createdAt')) }}">
        {{ date('Y-m-d', $page->get('createdAt')) }}
      </time>
    @else
      <a
        href="{{ ($page->lang ?? 'en') === 'en' ? $page->getBaseUrl() . '/en/feed.json' : $page->getBaseUrl(). '/br/feed.json' }}"
        class="feed"
      >
        JSON Feed
      </a>
    @endif
    <nav class="align-right">
      <a href="/br">🇧🇷</a> | <a href="/en">🇬🇧</a>
    </nav>
  </header>
<h1>{{ $page->get('title') }}</h1>

@yield('body')
</body>
<script src="{{ mix('js/main.js', 'assets/build') }}"></script>
</html>
