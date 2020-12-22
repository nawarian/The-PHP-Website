@php
$page->canonical = 'https://thephp.website/en/';
@endphp

@extends('_layouts.master')

@section('body')
<h2>Highlights</h2>
<div class="card">
  <a href="{{ $featuredPublication->getUrl() . '/' }}">
    <div class="card__content">
      @if ($featuredPublication->get('image'))
        <div
          class="card__image"
          style="background-image: url({{ $featuredPublication->get('image')['url'] }})"
          alt="{{ $featuredPublication->get('image')['alt'] }}"
        ></div>
      @endif
      <h3>{{ $featuredPublication->title }}</h3>
      <p>{{ $featuredPublication->meta['description'] }}</p>
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
