<ul class="card-list">
  @foreach ($jobs as $job)
    @include('_layouts.partials.job-card', ['job' => $job])
  @endforeach
</ul>
