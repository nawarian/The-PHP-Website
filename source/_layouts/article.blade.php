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
