---
extends: _layouts.master
title: Vagas de Emprego de Programação em PHP
pagination:
  collection: jobs_pt_br
  perPage: 4
---

@section('body')
  @include('_layouts.partials.pagination', [
    'pagination' => $pagination,
    'page' => $page,
  ])

  @include('_layouts.partials.job-card-list', ['jobs' => $pagination->items])

  <hr />

  @include('_layouts.partials.pagination', [
    'pagination' => $pagination,
    'page' => $page,
  ])
@endsection
