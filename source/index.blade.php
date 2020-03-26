@extends('_layouts.master')

@section('body')
<h2>Highlights</h2>
<div class="card">
  <a href="{{ $latestHighlight->getUrl() . '/' }}">
    <div class="card__content">
      <h3>{{ $latestHighlight->title }}</h3>
      <p>{{ $latestHighlight->meta['description'] }}</p>
      <time>{{ date('Y-m-d', $latestHighlight->createdAt) }}</time>
    </div>
  </a>
</div>

<h2>Latest issues</h2>
<ul class="card-list">
  @foreach($page->get('latestIssues') as $post)
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
          <h3>{{ $post->title }}</h3>
          <p>{{ $post->meta['description'] }}</p>
          <time>{{ date('Y-m-d', $post->createdAt) }}</time>
        </div>
      </a>
    </li>
  @endforeach
</ul>
@endsection
