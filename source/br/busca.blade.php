@php
$page->lang = 'pt-br';
$page->isArticle = false;
@endphp

@extends('_layouts.master')

@section('head.link')
  <link rel="stylesheet" href="{{ mix('css/search.css', 'assets/build') }}" />
  <link rel="prefetch" href="/search-index.json" />
@endsection

@section('body')
  <form action="#" name="search" class="search-form">
    <h1 class="search-form__title">Encontre artigos no diretório do thephp.website!</h1>
    <input id="search-box" class="search-form__input" type="search" placeholder="ex.: 'php 8'" autofocus />
  </form>

  <noscript
    style="background-color: yellow; display: block; padding: 20px; border-radius: 20px; font-weight: bold; margin-top: 20px;"
  >
    A busca só funciona no front-end. Por favor, habilite o javascript para usar esta funcionalidade!
  </noscript>

  <section class="search-results"></section>

  <script src="{{ mix('js/search.js', 'assets/build') }}" async></script>
@endsection
