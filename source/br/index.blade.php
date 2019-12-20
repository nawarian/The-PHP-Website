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
        <h3>{{ $post->title }}</h3>
        <p>{{ $post->meta['description'] }}</p>
        <small>{{ date('d/m/Y', $post->createdAt) }}</small>
      </a>
    </li>
  @endforeach
</ul>

<hr />
<h2>Vagas de Emprego de programação em PHP</h2>
<ul class="card-list">
  @foreach($page->get('latestJobsBr') as $post)
    <li class="card">
      <a href="{{ $post->getUrl() }}"><h3>{{ $post->title }}</h3></a>
      <small>Publicado em {{ date('d/m/Y', $post->createdAt) }}</small>
    </li>
  @endforeach
</ul>
@endsection
