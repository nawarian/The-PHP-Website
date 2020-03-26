@php
  $page->lang = 'pt-br';
@endphp

@extends('_layouts.master')

@section('body')
<h2>Em Destaque</h2>
<div class="card">
  <a href="{{ $latestHighlightBr->getUrl() . '/' }}">
    <div class="card__content">
      <h3>{{ $latestHighlightBr->title }}</h3>
      <p>{{ $latestHighlightBr->meta['description'] }}</p>
      <time>{{ date('d/m/Y', $latestHighlightBr->createdAt) }}</time>
    </div>
  </a>
</div>

<h2>Últimas postagens</h2>
<ul class="card-list">
  @foreach($page->get('latestIssuesBr') as $post)
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
@include('_layouts.partials.job-card-list', ['jobs' => $page->get('latestJobsBr')])
<a href="{{ $page->getBaseUrl() . '/br/vagas/' }}">Ver mais vagas...</a>
@endsection
