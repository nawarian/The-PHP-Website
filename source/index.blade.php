@extends('_layouts.master')

@section('body')
<h1>Latest issues</h1>
<ul class="card-list">
  @foreach($page->get('latestIssues') as $post)
    <li class="card">
      <a href="{{ $post->getUrl() }}">
        <h3>{{ $post->title }}</h3>
        <p>{{ $post->meta['description'] }}</p>
        <small>{{ date('Y-m-d', $post->createdAt) }}</small>
      </a>
    </li>
  @endforeach
</ul>
@endsection
