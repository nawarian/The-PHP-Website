@extends('_layouts.master')

@section('body')
<h1>Latest issues</h1>
<ul>
  @foreach($page->get('latestIssuesBr') as $post)
    <li>
      <a href="{{ $post->getUrl() }}">[{{ date('Y-m-d', $post->createdAt) }}] - {{ $post->title }}</a>
    </li>
  @endforeach
</ul>
@endsection
