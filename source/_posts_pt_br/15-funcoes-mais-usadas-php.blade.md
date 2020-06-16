---
slug: funcoes-mais-usadas-php
lang: pt-br
title: Quais são as top 20 funções do php mais usadas pelos frameworks?
category: thoughts
createdAt: 2020-06-16
sitemap:
  lastModified: 2020-06-16
image:
  url: '/assets/images/posts/15-question-mark-640.webp'
  alt: 'Um ponto de interrogação enorme e cenralizado na imagem.'
tags:
  - curiosidade
  - core
  - relatorio
  - top
meta:
  description:
    Por curiosidade eu decidir rankear a utilização das funções
    nativas do php nos frameworks mais populares.
  twitter:
    card: summary
    site: '@nawarian'
---

[Read in English](/en/issue/most-used-php-functions/)

Eu me questiono muito sobre o quão útil este post é. Mas
eu acho que faz sentido compartilhá-lo e também o código
que me ajudou a alcançar estes números.

Este post também é pra sinalizar que eu só estive ocupado,
mas não desisti do projeto do site 😉. 

Abaixo eu mostro as top 20 funções mais utilizadas por
frameworks php.

Você pode encontrar o código que usei pra obter estes
dados neste
[repositório do github](https://github.com/nawarian/The-PHP-Website/tree/master/code/15-framework-functions/).

Eu coletei estes dados baseado nos seus branches principais
na data de hoje. (16/06/2020)

Sinta-se livre pra me [pingar no twitter](https://twitter.com/nawarian)
se você quiser ver outro framework nesta lista aqui! 😊

## Pedido atentido: Top dos top!

Já que uma galera pediu, aqui vai uma lista das top 5
funções entre todas as listas aqui.

Eu vou pular o `sprintf()` porque o symfony sozinho
joga o número pra `2_743` e ficaria meio injusto.

A lista compiladinha é a seguinte:

Função | Utilizações
-------- | ------
is_array | 1414
count | 1102
strpos | 1001
substr | 974
implode | 845

Eu devo dizer que tô bem impressionado sobre o quanto usamos
o `is_array()` e `count()`. Eu testei em alguns repositórios
que eu trabalho e em alguns o número de chamadas ao `count()`
fica em torno de 3 mil.

Só coisa boa 🤣

## Top 20 funções php utilizadas no Symfony

Função | Utilizações
------ | -----------
sprintf | 2743
substr | 708
strpos | 594
count | 588
is_array | 572
strlen | 445
implode | 436
class_exists | 415
is_string | 338
preg_match | 336
in_array | 333
str_replace | 310
array_merge | 271
array_keys | 234
get_class | 224
array_key_exists | 193
explode | 189
is_object | 164
preg_replace | 157
strtolower | 154

Interessante notar que o `sprintf` é a função mais utilizada.
Provavelmente por conta da forma como o
[coding standard do Symfony](https://symfony.com/doc/current/contributing/code/standards.html)
lida com o lançamento de exceções. 

> Exceções e mensagens de erro dever ser concatenadas utilizando sprintf;

## Top 20 funções php utilizadas pelo Laravel

Função | Utilizações
------ | -----------
is_null | 440
is_array | 243
array_merge | 196
func_get_args | 155
str_replace | 146
count | 143
is_string | 129
in_array | 120
explode | 119
trim | 111
method_exists | 97
implode | 91
get_class | 84
function_exists | 81
compact | 75
array_map | 72
is_numeric | 69
sprintf | 68
call_user_func | 61
array_values | 58

## Top 20 funções php utilizadas pelo Amp

Função | Utilizações
------ | -----------
is_int | 11
assert | 27
range | 26
microtime | 17
array_shift | 14
get_class | 11
debug_backtrace | 11
call_user_func_array | 11
sprintf | 10
getmypid | 9
posix_kill | 9
is_resource | 9
printf | 6
fwrite | 6
usleep | 6
count | 8
stream_socket_pair | 5
gc_collect_cycles | 5
substr | 5
defined | 5

Eu acho meio injusto rodar esse programa no Amp ou Laminas
porque eles são compostos por vários pacotes diferentes de uma vez.
Então o repositório raiz não tá realmente pegando todas as dependências.

Mas eu ainda acho bacana a diferença na natureza dessas chamadas
em comparação com os outros frameworks ^^.

## Top 20 funções php utilizadas pelo Cake PHP

Função | Utilizações
------ | -----------
sprintf | 480
is_array | 367
strpos | 235
implode | 233
count | 216
is_string | 206
in_array | 198
substr | 172
explode | 159
array_merge | 139
str_replace | 133
preg_match | 108
strtolower | 98
array_keys | 97
strlen | 95
array_filter | 91
is_numeric | 80
array_map | 71
is_int | 68
array_key_exists | 67

## Top 20 funções php utilizadas pelo Code Igniter 4

Função | Utilizações
------ | -----------
is_array | 232
function_exists | 182
strpos | 172
str_replace | 151
count | 147
in_array | 141
is_null | 127
trim | 125
strlen | 110
explode | 105
is_string | 105
strtolower | 104
preg_match | 97
array_key_exists | 91
substr | 89
implode | 86
rtrim | 64
preg_replace | 64
defined | 60
define | 57

<div class="align-right">
  --
  <a href="https://twitter.com/nawarian" rel="nofollow">
    @nawarian
  </a>
</div>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "TechArticle",
  "headline": "Quais são as top 20 funções do php mais usadas pelos frameworks?",
  "description": "Por curiosidade eu decidir rankear a utilização das funções nativas do php nos frameworks mais populares.",
  "image": [
    "{{ $page->getBaseUrl() }}/assets/images/posts/15-question-mark-640.webp"
   ],
  "datePublished": "2020-06-16T00:00:00+08:00",
  "dateModified": "2020-04-16T00:00:00+08:00",
  "author": {
    "@type": "Person",
    "name": "Nawarian Níckolas Da Silva"
  },
   "publisher": {
    "@type": "Organization",
    "name": "ThePHP Website",
    "logo": {
      "@type": "ImageObject",
      "url": "https://thephp.website/favicon.ico"
    }
  }
}
</script>
