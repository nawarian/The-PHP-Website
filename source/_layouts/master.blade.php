<!DOCTYPE html>
<html lang="{{ $page->lang ?? 'en' }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <link rel="stylesheet" href="{{ mix('css/main.css', 'assets/build') }}">
  <link
    rel="alternate"
    type="application/json"
    title="{{ ($page->lang ?? 'en') === 'en' ? '[EN]' : '[BR]' }} thePHP Website"
    href="{{ ($page->lang ?? 'en') === 'en' ? $page->getBaseUrl() . '/en/feed.json' : $page->getBaseUrl(). '/br/feed.json' }}"
  />
  <link
    rel="alternate"
    type="application/rss+xml"
    title="{{ ($page->lang ?? 'en') === 'en' ? '[EN]' : '[BR]' }} thePHP Website"
    href="{{ ($page->lang ?? 'en') === 'en' ? $page->getBaseUrl() . '/en/feed.xml' : $page->getBaseUrl(). '/br/feed.xml' }}"
  />
  @yield('head.link')

  <title>{{ $page->title ? $page->title . ' | thePHP Website' : 'thePHP Website' }}</title>

  @include('_layouts.partials.meta')

  <!-- Global site tag (gtag.js) - Google Analytics -->
  <script async src="https://www.googletagmanager.com/gtag/js?id={{ $page->get('gaId')[$page->lang ?? 'en'] }}"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', '{{ $page->get('gaId')[$page->lang ?? 'en'] }}');
  </script>
  <!-- Hotjar Tracking Code for thephp.website -->
  <script>
    (function(h,o,t,j,a,r){
        h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};
        h._hjSettings={hjid:1770868,hjsv:6};
        a=o.getElementsByTagName('head')[0];
        r=o.createElement('script');r.async=1;
        r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;
        a.appendChild(r);
    })(window,document,'https://static.hotjar.com/c/hotjar-','.js?sv=');
</script>
</head>
<body class="yue {{ ($page->get('isArticle') ?? false) ? 'non-article' : 'non-article' }}">
  <nav class="menu">
    <input type="checkbox" id="menu-toggle" class="menu__toggle" />
    <label for="menu-toggle" class="menu__toggle-button">
      <img class="menu__toggle-button--inactive" src="/assets/images/burger-menu-icon.png" alt="Open Menu">
      <img class="menu__toggle-button--active" src="/assets/images/x-icon.png" alt="Close Menu">
    </label>
    <div class="menu__header">
      <a href="{{ ($page->lang ?? 'en') === 'en' ? '/en/' : '/br/' }}">
        thePHP Website
      </a>
    </div>

    <div class="menu__container">
      <em class="menu__section-heading">
        Pages.php
      </em>
      <ul class="menu-list">
        <li class="menu__list-item">
          <a href="{{ ($page->lang ?? 'en') === 'en' ? '/en/' : '/br/' }}">Home</a>
        </li>
        <li class="menu__list-item">
          <a
            href="https://github.com/nawarian/The-PHP-Website/issues/new?title=[Suggested+Topic]%20&body=Please+describe+your+suggestion+/+Por+favor+descreva+sua+sugestão"
            rel="nofollow"
          >
            {{ ($page->lang ?? 'en') === 'en' ? 'Suggest a Topic!' : 'Peça um Post!' }}
          </a>
        </li>
        <li class="menu__list-item">
          <a href="{{ ($page->lang ?? 'en') === 'en' ? '/en/about/' : '/br/sobre/' }}">
            {{ ($page->lang ?? 'en') === 'en' ? 'about.php' : 'sobre.php' }}
          </a>
        </li>
      </ul>

      <hr>
      <em class="menu__section-heading">
        Languages.php
      </em>
      <ul class="menu-list">
        <li class="menu__list-item">
          <a href="/en/">English</a>
        </li>
        <li class="menu__list-item">
          <a href="/br/">Português</a>
        </li>
      </ul>

      <hr>
      <em class="menu__section-heading">
        Notifications.php
      </em>
      <ul class="menu-list">
        <li class="menu__list-item">
          <a href="{{ $page->getBaseUrl() }}{{ ($page->lang ?? 'en') === 'en' ? '/en/feed.json' : '/br/feed.json' }}">
            {{ ($page->lang ?? 'en') === 'en' ? '[EN]' : '[BR]' }} JSON Feed
          </a>
        </li>
        <li class="menu__list-item">
          <a href="{{ $page->getBaseUrl() }}{{ ($page->lang ?? 'en') === 'en' ? '/en/feed.xml' : '/br/feed.xml' }}">
            {{ ($page->lang ?? 'en') === 'en' ? '[EN]' : '[BR]' }} Atom Feed (RSS)
          </a>
        </li>
        @if ($page->lang ?? 'en' === 'pt-br')
        <li class="menu__list-item">
          <a href="{{ $page->getBaseUrl() }}/br/feed-vagas.xml">
            Feed de Vagas (RSS)
          </a>
        </li>
        @endif
      </ul>
    </div>
  </nav>

  <article class="container">
    @yield('body')
  </article>
</body>
<script src="{{ mix('js/main.js', 'assets/build') }}"></script>
</html>
