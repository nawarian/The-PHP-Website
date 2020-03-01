---
extends: _layouts.master
title: Guides and tutorials about PHP's ecosystem
lang: pt-br
pagination:
  collection: guides_en
  perPage: 12
---

@section('body')
  <h1>Guides and tutorials about PHP's ecosystem</h1>
  <ul class="card-list">
    @foreach($pagination->items as $post)
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

  @include('_layouts.partials.pagination', [
    'pagination' => $pagination,
    'page' => $page,
  ])
@endsection
