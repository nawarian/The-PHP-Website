@php
$page->lang = 'en';
$page->isArticle = false;
@endphp

@extends('_layouts.master')

@section('head.link')
  <link rel="stylesheet" href="{{ mix('css/search.css', 'assets/build') }}" />
  <link rel="prefetch" href="/search-index.json" />
@endsection

@section('body')
  <form action="#" name="search" class="search-form">
    <h1 class="search-form__title">Find articles in thephp.website's directory!</h1>
    <input id="search-box" class="search-form__input" type="search" placeholder="e.g. 'php 8'" autofocus />
  </form>

  <noscript
    style="background-color: yellow; display: block; padding: 20px; border-radius: 20px; font-weight: bold; margin-top: 20px;"
  >
    The search is client-side only. Please enable javascript for it to work properly!
  </noscript>

  <section class="search-results"></section>

  <script src="{{ mix('js/search.js', 'assets/build') }}" async></script>
@endsection
