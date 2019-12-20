@php
  $page->lang = 'pt-br';
@endphp

@extends('_layouts.master')

@section('body')
<h1>Últimas postagens</h1>
<ul class="card-list">
  @foreach($page->get('latestIssuesBr') as $post)
    <li class="card">
      <a href="{{ $post->getUrl() }}">
        @if ($post->get('image'))
          <img src="{{ $post->get('image')['url'] }}" alt="{{ $post->get('image')['alt'] }}" />
        @endif

        <div class="card__content">
          <h3 class="card__content-heading">{{ $post->title }}</h3>
          <p>{{ $post->meta['description'] }}</p>
          <time>{{ date('d/m/Y', $post->createdAt) }}</time>
        </div>
      </a>
    </li>
  @endforeach
</ul>

<hr />
<h2>Vagas de Emprego de programação em PHP</h2>
<ul class="card-list">
  @foreach($page->get('latestJobsBr') as $post)
    <li class="card">
      <a href="{{ $post->getUrl() }}">
        <div class="card__content">
          <h3>{{ $post->title }}</h3>
          <small>Publicado em {{ date('d/m/Y', $post->createdAt) }}</small>
        </div>
      </a>
    </li>
  @endforeach
</ul>
@endsection
