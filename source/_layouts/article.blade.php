@extends('_layouts.master')

@section('body')
  <div class="article">
    <time datetime="{{ date('Y-m-d', $page->get('createdAt')) }}">
      {{ date('Y-m-d', $page->get('createdAt')) }}
    </time>

    <h1>{{ $page->get('title') }}</h1>
    @yield('content')
  </div>
@endsection

@section('aside')
  @if(count($page->get('recommendations') ?? []) > 0)
    <div class="recommended">
      <h3>Keep reading</h3>
      <ul class="card-list">
        @foreach($page->get('recommendations') ?? [] as $post)
          <li class="card">
            <a href="{{ $post->getUrl() . '/' }}">
              @if ($post->get('image'))
                <div
                        class="card__image"
                        style="background-image: url({{ $post->get('image')['url'] }})"
                        alt="{{ $post->get('image')['alt'] }}"
                ></div>
              @endif

              <div class="card__content">
                <h4>{{ $post->title }}</h4>
                <p>{{ $post->meta['description'] }}</p>
                <time>{{ date('Y-m-d', $post->createdAt) }}</time>
              </div>
            </a>
          </li>
        @endforeach
      </ul>
    </div>
  @endif
@endsection
