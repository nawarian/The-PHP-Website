<li class="card">
  <a href="{{ $job->getUrl() }}">
    <div class="card__content">
      <h3>{{ $job->title }}</h3>
      @if ($job->get('meta')['description'] !== $job->get('title'))
        <p>
          {{ substr($job->get('meta')['description'], 0, 150) }}...
        </p>
      @endif
      <p><u>Ler mais</u></p>
      <small>Publicado em {{ date('d/m/Y', $job->createdAt) }}</small>
    </div>
  </a>
</li>
