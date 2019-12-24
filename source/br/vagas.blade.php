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

  <ul class="card-list">
    @foreach ($pagination->items as $post)
      <li class="card">
        <a href="{{ $post->getUrl() }}">
          <div class="card__content">
            <h3>{{ $post->title }}</h3>
            @if ($post->get('meta')['description'] !== $post->get('title'))
              <p>
                {{ substr($post->get('meta')['description'], 0, 150) }}...
              </p>
            @endif
            <p><u>Ler mais</u></p>
            <small>Publicado em {{ date('d/m/Y', $post->createdAt) }}</small>
          </div>
        </a>
      </li>
    @endforeach
  </ul>

  <a href="{{ $page->getBaseUrl() . $pagination->previous }}">&lt;</a>
  @foreach ($pagination->pages as $number => $jobHref)
    <a href="{{ $page->getBaseUrl() . $jobHref }}">{{ $number }}</a>
  @endforeach
  <a href="{{ $page->getBaseUrl() . $pagination->next }}">&gt;</a>
@endsection
