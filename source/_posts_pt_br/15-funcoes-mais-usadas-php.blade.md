---
slug: funcoes-mais-usadas-php
lang: pt-br
title: Quais são as top 20 funções do php mais usadas pelos frameworks?
category: thoughts
createdAt: 2020-06-16
sitemap:
  lastModified: 2020-06-27
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

<s>Eu me questiono muito sobre o quão útil este post é. Mas
eu acho que faz sentido compartilhá-lo e também o código
que me ajudou a alcançar estes números.</s> Muitíssimo obrigado
pelo seu feedback, esta lista ficou muito mais massa do
que eu poderia ter imaginado e eu estou muito contente de
como ela foi recebida pela comunidade.

Este post também é pra sinalizar que eu só estive ocupado,
mas não desisti do projeto do site 😉. 

Abaixo eu mostro as top 20 funções mais utilizadas por
frameworks php.

Você pode encontrar o código que usei pra obter estes
dados neste
[repositório do github](https://github.com/nawarian/The-PHP-Website/tree/master/code/15-framework-functions/).

<s>Eu coletei estes dados baseado nos seus branches principais
na data de hoje. (16/06/2020)</s>

**Edit (2020-06-27):** como solicitado, o framework Yii2 foi adicionado
hoje e a lista toda foi atualizada. Então todo código analizado aqui se refere
ao branch principal na data de hoje. Ao mesmo tempo, eu decidi também
adicionar o PHPUnit e o WordPress na lista já que muitos de vocês
apresentaram curiosidade sobre eles. 

**Edit (2020-07-23):** atendendo a pedidos, Mangento 2 agora também
faz parte da lista! Os resultados são bem interessantes na miha opinião.
Ele jogou fora o `strpos()` do top 5 e trouxe o `implode()` em seu lugar ^^

Sinta-se livre pra me [pingar no twitter](https://twitter.com/nawarian)
se você quiser ver outro framework nesta lista aqui! 😊

## Pedido atentido: Top dos top!

Já que uma galera pediu, aqui vai uma lista das top 5
funções entre todas as listas aqui.

Eu vou pular o `sprintf()` porque o symfony sozinho
joga o número pra `2_746` e ficaria meio injusto.

A lista compiladinha é a seguinte:

Função | Utilizações
-------- | ------
is_array | 3943
substr | 3784
count | 2948
in_array | 2729
implode | 2334

Eu devo dizer que tô bem impressionado sobre o quanto usamos
o `is_array()` e `count()`. Eu testei em alguns repositórios
que eu trabalho e em alguns o número de chamadas ao `count()`
fica em torno de 3 mil.

Só coisa boa 🤣

## Top 20 funções php utilizadas no WordPress

Função | Utilizações
------ | -----------
substr | 2200
sprintf | 1939
in_array | 912
is_array | 769
strpos | 667
printf | 633
define | 584
str_replace | 572
trim | 566
count | 556
strlen | 541
preg_match | 501
defined | 432
function_exists | 430
implode | 417
preg_replace | 412
explode | 385
array_merge | 372
array_keys | 275
is_string | 275

## Top 20 funções php utilizadas no Magento 2

Função | Utilizações
------ | -----------
is_array | 1420
sprintf | 1141
count | 1045
array_merge | 976
in_array | 901
implode | 822
array_keys | 697
explode | 674
trim | 491
str_replace | 475
array_key_exists | 441
substr | 367
strlen | 358
strpos | 358
is_string | 330
preg_match | 296
strtolower | 293
json_encode | 269
get_class | 267
is_numeric | 262

## Top 20 funções php utilizadas no PHPUnit

Função | Utilizações
------ | -----------
sprintf | 188
func_get_args | 183
count | 90
strpos | 49
explode | 43
trim | 39
class_exists | 39
get_class | 39
assert | 38
file_get_contents | 34
implode | 32
preg_match | 32
substr | 32
is_string | 31
is_array | 29
array_merge | 26
strlen | 26
in_array | 23
str_replace | 21
is_object | 17

## Top 20 funções php utilizadas no Symfony

Função | Utilizações
------ | -----------
sprintf | 2746
substr | 715
strpos | 602
count | 590
is_array | 573
strlen | 446
implode | 438
class_exists | 415
is_string | 338
preg_match | 338
in_array | 336
str_replace | 310
array_merge | 271
array_keys | 235
get_class | 226
array_key_exists | 193
explode | 192
is_object | 164
preg_replace | 157
strtolower | 154

Interessante notar que o `sprintf` é a função mais utilizada.
Provavelmente por conta da forma como o
[coding standard do Symfony](https://symfony.com/doc/current/contributing/code/standards.html)
lida com o lançamento de exceções. 

> Exceções e mensagens de erro dever ser concatenadas utilizando sprintf;

## Top 20 functions used by Yii2

Função | Utilizações
-------- | ------
is_array | 300
implode | 210
strpos | 182
substr | 172
array_merge | 150
count | 147
is_string | 140
trim | 118
preg_match | 107
str_replace | 104
get_class | 104
call_user_func | 103
in_array | 91
array_keys | 90
strncmp | 77
explode | 73
preg_replace | 66
strlen | 62
array_key_exists | 62
reset | 56

## Top 20 funções php utilizadas pelo Laravel

Função | Utilizações
------ | -----------
is_null | 450
is_array | 250
array_merge | 203
func_get_args | 155
count | 149
str_replace | 148
is_string | 132
in_array | 126
explode | 120
trim | 110
method_exists | 102
implode | 93
get_class | 86
function_exists | 79
compact | 75
array_map | 72
is_numeric | 71
sprintf | 68
call_user_func | 63
array_values | 61

## Top 20 funções php utilizadas pelo Amp

Função | Utilizações
------ | -----------
assert | 27
range | 26
microtime | 17
array_shift | 14
debug_backtrace | 11
get_class | 11
is_int | 11
call_user_func_array | 11
sprintf | 10
is_resource | 9
posix_kill | 9
getmypid | 9
count | 8
usleep | 6
fwrite | 6
printf | 6
stream_socket_pair | 5
gc_collect_cycles | 5
defined | 5
substr | 5

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
is_string | 105
explode | 105
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
  "dateModified": "2020-06-27T00:00:00+08:00",
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
