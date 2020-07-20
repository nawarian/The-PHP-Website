---
lang: pt-br
slug: como-escrever-crawlers-em-php
title: Como escrever crawlers decentes com PHP
category: guides
createdAt: 2020-07-20
sitemap:
  lastModified: 2020-07-20
image:
  url: /assets/images/posts/16-many-books-and-magazines-640.webp
  alt: 'Muitos livros e revistas.'
tags:
  - crawlers
  - guia
meta:
  description:
    Depois deste artigo você vai perceber o quanto você sofreu com
    seus crawlers em PHP. EXISTE uma forma melhor. Deixa eu lhe
    mostrar 😉
  twitter:
    card: summary
    site: '@nawarian'
---

[Read in English](/en/issue/how-to-write-crawlers-with-php)

Você provavelmente já viu vários posts sobre como escrever crawlers com php.
O que difere este post dos outros? Eu garanto que você não precisa se malucar
com expressões regulares, variáveis globais e todo esse tipo de coisa irritante.

Nós vamos usar uma ferramenta maravilhosa chamada `spatie/crawler` que vai nos
forcnecer uma ótima interface para escrever crawlers sem ir à loucura!

**Abaixo tem um vídeo meu codificando este crawler. É só rolar a página até o
vídeo se tu quiser pular direto pra ação. 😉**

## Nosso caso de uso

Este crawler vai ser bem simplão e pretende buscar nomes, apelidos e e-mails
do diretório oficial do PHP sobre pessoas que contribuíram com a linguagem de
alguma forma.

Você pode olhar o repositório nesta url aqui: [https://people.php.net](https://people.php.net).

## Configurando o ambiente

Montar o ambiente vai ser bem rápido, eu vou só copiar as sessões _composer_
e _php_ desse outro post que eu escrevi sobre [como montar um ambiente com docker rapidex](/br/edicao/php-docker-setup-rapido).

Meu arquivo _docker-compose.yml_ ficou assim:

```yaml
version: '3'
services:
  composer:
    image: composer:1.9.3
    environment:
      - COMPOSER_CACHE_DIR=/app/.cache/composer
    volumes:
      - .:/app
    restart: never

  php:
    image: php:7.4-cli
    restart: never
    volumes:
      - .:/app
    working_dir: /app
```

Agora vamos instalar os pacotes:

```bash
$ docker-compose run \
  composer require \
    spatie/crawler \
    symfony/css-selector
```

Tudo o que a gente precisa agora é um arquivo pra executar, vamos criar
um arquivo bin/crawler.php:

```bash
$ mkdir bin
$ touch bin/crawler.php
```

Massa! Agora vamos adicionar o autoload nesse arquivo e estamos prontos pra começar:

```php
// bin/crawler.php
<?php

require_once __DIR__ . 
  '/../vendor/autoload.php';
```

De agora em diante a gente pode rodar nosso crawler com o seguinte comando:

```bash
$ docker-compose run php \
  php bin/crawler.php
```

## Vamos analizar o site alvo

Normalmente a gente deveria navegar pelo website e entender como ele funciona:
padrões de url, chamadas ajax, tokens csrf, se feeds ou APIs estão disponíveis.

Neste caso nenhuma das opções está disponível. A gente precisa criar um crawler
cruzão mesmo que vai buscar páginas em HTML e interpretá-las.

Eu vejo alguns padrões de URL:
- Página de perfil: people.php.net/{nickname}
- Página de diretório: people.php.net/?page={number}
- Links externos

Parece simples! A gente só precisa se preocupar em interpretar o HTML dentro
de páginas de perfil e ignorar o restante.

Ao verificar a página de perfil podemos perceber rapidamente que os seletores
importantes pra gente são:
- Nome: `h1[property=foaf:name]`
- Apelido: `h1[property=foaf:nick]`

A gente também pode confiar que o e-mail das pessoas segue o padrão "{apelido}@php.net".

Com essa informação, bora codar!

## Obtendo dados públicos de todas as pessoas que contribuíram com o PHP 

Abaixo você encontra o código, mas se você prefere mais vídeos, dá uma ligadinha
nesse aqui que eu fiz pra ti:

<iframe style="margin: auto;" width="560" height="315" src="https://www.youtube.com/embed/HaMoYhTV1hI?start=21" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

## Mão na massa!

O pacote `spatie/crawler` traz duas classes abstratas muito importantes - que
eu adoraria que fossem interfaces.

Uma delas é a classe `CrawlObserver`, onde a gente pode se conectar aos passos
de obter uma página e manipular respostas http. A nossa lógica entra aqui.

Eu vou escrever um observer rapidinho com uma classe anônima abaixo:

```php
$observer = new class
  extends CrawlObserver
{
  public function crawled(
    $url,
    $response,
    $foundOnurl
  ) {
    $domCrawler = new DomCrawler(
      (string) $response->getBody()
    );
    
    $name = $domCrawler
      ->filter('h1[property="foaf:name"]')
      ->first()
      ->text();
    $nick = $domCrawler
      ->filter('h2[property="foaf:nick"]')
      ->first()
      ->text();
    $email = "{$nick}@php.net";
    
    echo "[{$email}] {$name} - {$nick}" . PHP_EOL;
  }
};
```

A lógica acima vai buscar as propriedades que esperamos das páginas de
perfil. É claro que a gente deveria também verificar se estamos na página
correta ou não.

Agora, o próximo passo importante é a classe abstrata `CrawlProfile`. Com
esta classe a gente consegue decidir se uma URL deveria ou não ser acessada
por um observer. Vamos criar também como classe anônima:

```php
$profile = new class
  extends CrawlProfile
{
  public function shouldCrawl(
    $url
  ): bool {
    return $url->getHost() ===
      'people.php.net';
  }
};
```

Acima a gente definiu que queremos seguir apenas links internos. Isso porque
esse website cria links pra vários outros repositórios. E a gente não quer
crawlear todo o universo php, certo?

Com essas duas instâncias em mãos, podemos já preparar o crawler e iniciar
a busca:

```php
Crawler::create()
  ->setCrawlObserver($observer)
  ->setCrawlProfile($profile)
  ->setDelayBetweenRequests(500)
  ->startCrawling(
    'https://people.php.net/'
  );
```

**Importante!** Reparou naquele `setDelayBetweenRequests(500)`? Ele faz com que
o crawler vá buscar apenas uma URL a cada 500 milisegundos. Isso é porque a gente
não quer derrubar esse site, certo? (Sérião, não derruba esse site. Se tu quer fazer
maldade, busca um site do governo ou coisa do gênero 👀)

## E é isso!

Rápido e prático, e mais importante de tudo: sem loucuras! O `spatie/crawler` tem uma
interface muito massa que simplifica demais o processo.

Se você juntar essa ferramenta com uma injeção de dependências e enfileiramento você
terá resultados profissionais.

Me dá um toque no twitter se você tiver dúvidas!
Uma abraço! 👋

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
  "headline": "Como escrever crawlers decentes com PHP",
  "description": "Depois deste artigo você vai perceber o quanto você sofreu com seus crawlers em PHP. EXISTE uma forma melhor.",
  "image": [
    "{{ $page->getBaseUrl() }}/assets/images/posts/16-many-books-and-magazines-640.webp"
   ],
  "datePublished": "2020-07-20T00:00:00+08:00",
  "dateModified": "2020-07-20T00:00:00+08:00",
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
