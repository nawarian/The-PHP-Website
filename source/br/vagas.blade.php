---
extends: _layouts.master
pagination:
  collection: jobs_pt_br
  perPage: 6
---

@section('body')
  <a href="{{ $page->getBaseUrl() . $pagination->previous }}">&lt;</a>
  @foreach ($pagination->pages as $number => $jobHref)
    <a href="{{ $page->getBaseUrl() . $jobHref }}">{{ $number }}</a>
  @endforeach
  <a href="{{ $page->getBaseUrl() . $pagination->next }}">&gt;</a>

  @include('_layouts.partials.job-card-list', ['jobs' => $pagination->items])

  <a href="{{ $page->getBaseUrl() . $pagination->previous }}">&lt;</a>
  @foreach ($pagination->pages as $number => $jobHref)
    <a href="{{ $page->getBaseUrl() . $jobHref }}">{{ $number }}</a>
  @endforeach
  <a href="{{ $page->getBaseUrl() . $pagination->next }}">&gt;</a>
@endsection
