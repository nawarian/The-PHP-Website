@if ($page->get('meta') && isset($page->get('meta')['description']))
  <meta name="description" content="{{ $page->get('meta')['description'] }}">
@endif

@if ($page->get('meta'))
  <meta property="og:title" content="{{ $page->get('meta')['opengraph']['title'] ?? $page->get('title') }}">
  <meta property="og:description" content="{{ $page->get('meta')['opengraph']['description'] ?? $page->get('meta')['description'] }}">
  <meta property="og:url" content="{{ $page->getUrl() }}">
@endif
@if ($page->get('image'))
  <meta property="og:image" content="{{ $page->getBaseUrl() . $page->get('image')['url'] }}">
@endif

@if ($page->get('meta') && isset($page->get('meta')['twitter']))
  <meta name="twitter:card" content="{{ $page->get('meta')['twitter']['card'] }}">
  <meta name="twitter:site" content="{{ $page->get('meta')['twitter']['site'] }}">
  <meta name="twitter:title" content="{{ $page->get('meta')['twitter']['title'] ?? $page->get('title') }}">
  <meta name="twitter:description" content="{{ $page->get('meta')['twitter']['description'] ?? $page->get('meta')['description'] }}">
@endif
