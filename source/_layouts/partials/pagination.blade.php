<ol class="pagination">
  <li class="pagination__prev-next">
    <a href="{{ $page->getBaseUrl() . $pagination->previous }}">&lt;</a>
  </li>
  @foreach ($pagination->pages as $number => $jobHref)
    <li class="pagination__item">
      <a href="{{ $page->getBaseUrl() . $jobHref }}">{{ $number }}</a>
    </li>
  @endforeach
  <li class="pagination__prev-next pagination__prev-next--right">
    <a href="{{ $page->getBaseUrl() . $pagination->next }}">&gt;</a>
  </li>
</ol>
