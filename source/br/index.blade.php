@php
  $page->lang = 'pt-br';
@endphp

@extends('_layouts.master')

@section('body')
<h1>Últimas postagens</h1>
<ul>
  @foreach($page->get('latestIssuesBr') as $post)
    <li>
      <a href="{{ $post->getUrl() }}">[{{ date('Y-m-d', $post->createdAt) }}] - {{ $post->title }}</a>
    </li>
  @endforeach
</ul>
@endsection
