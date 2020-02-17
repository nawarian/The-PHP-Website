---
slug: php-docker-setup-rapido
lang: pt-br
title: Set up rápido do PHP, PHPUnit e Docker
createdAt: 2020-02-17
sitemap:
  lastModified: 2020-02-17
image:
  url: /assets/images/posts/7-container-640.webp
  alt: 'Um pulando sobre um conteiner'
tags:
  - aprendizado
  - phpunit
  - docker
meta:
  description:
    Neste post eu mostro rapidinho meu setup
    para aplicações php usando PHPUnit e Docker
    e algumas configs rápidas que quase todo app
    precisa.
  twitter:
    card: summary
    site: '@nawarian'
---

[Read in English](/en/issue/php-docker-quick-setup/)

Neste texto eu vou lhe mostrar alguns snippets
do meu setup básico pra iniciar aplicações PHP.

**Meu maior objetivo aqui é que você marque este
post nos seus favoritos** para que possa voltar,
copiar e colar as coisas daqui sempre que precisar
criar ou alterar suas aplicações php. 😉

A coisa legal de usar este setup é que você
pode facilmente trocar as versões das imagens
sem precisar configurar um montante de coisas
de uma vez.

Então...

**Antes de começar:** tenha certeza de que você
possui `docker` e `docker-compose` instalados.

## O resultado final

Se você seguir este tutorial, será capaz de executar
diferentes serviços através do comando `docker-compose`.

A maior ideia é que cada serviço pode ou não se tornar
um comando. E o formato se parece com o seguinte:

```bash
$ docker-compose run <comando> [--args]
```

Rodar uma suíte de testes, por exemplo, poderia se
parecer com isso:

```bash
$ docker-compose run tests
```

Pra tornar a digitação mais simples, podemos
também adicionar um alias para o comando
`docker-compose run`. Vou chamar de `dcr` aqui:

```bash
$ alias dcr='docker-compose run'
$ dcr lol
ERROR: Can't find a suitable
configuration file in this
directory or any parent.
Are you in the right directory?

Supported filenames:
docker-compose.yml,
docker-compose.yaml
```

Alias criado! O programa ainda vai reclamar porque
não existe um arquivo docker-compose ainda. Bora
criar então!

## Um docker-compose básico

Então a gente vai criar um projeto do zero, huh?
Bora lá! Comece **criando a pastsa do projeto** e
mais tarde **criando o arquivo docker-compose.yml**:

```bash
$ mkdir meu-projeto
$ cd meu-projeto
$ touch docker-compose.yml
```

Eu vou criar as pastas comuns que normalmente
minhas aplicações têm. Vai incluir pastas como
source, testes e binários.

Apenas execute o seguinte:

```bash
$ mkdir -p src/ tests/ bin/ \
  .conf/nginx/ var/
```

Agora podemos começar a trabalhar com o nosso
`docker-compose.yml`. Ele deverá conter todas
dependências que o nosso projeto teria.

O conteúdo inicial no nosso docker-compose será
bem simples. Apenas escreva o seguinte:

```yaml
# docker-compose.yml
version: '3'
services:
```

A gente vai escrever os serviços já agora! O mais
essencial de todos, como deveria ser, é o composer.

## Adicionando composer no docker-compose

Provavelmente usaremos o php de dentro do container.
Então **não faz sentido rodar o composer fora de um
container**, já que as versões do php podem divergir.

Vamos então adicionar um serviço `composer` ao nosso
arquivo:

```yaml
# docker-compose.yml
version: '3'
services:
  composer:
    image: composer:1.9.3
    environment:
      - COMPOSER_CACHE_DIR=/app/var/cache/composer
    volumes:
      - .:/app
    restart: never
```

O snippet acima vai criar um serviço `composer`,
que mapeia o diretório atual para `/app` dentro do container.

Definir a variável de ambiente COMPOSER_CACHE_DIR com
o valor `/app/var/cache/composer` fará com que o
composer escreva o cache na máquina local em vez de
somente dentro do container. Isto irá previnir que
o composer baixe todas dependências a cada execução.

Então é bom tomar conta de que a pasta `var/` nunca
vá parar no seu GIT, hein!

Só pra não esquecermos, vamos ignorar os arquivos
relacionados ao composer já agora. Apenas rode os
seguintes comandos pra evitar commitar esses caras:

```bash
$ echo 'vendor/' >> .gitignore
$ echo 'var/' >> .gitignore
```

Perfeito! Agora com o composer em mãos nós
estamos preparados para instalar a dependência
mais importante de todo proejto!

## Preparando o PHPUnit

A dependência mais importante deste skeleton app
é o motor de testes, é claro!

Vamos instalar o phpunit a partir do nosso
serviço `composer`:

```bash
$ dcr composer require --dev \
  phpunit/phpunit
```

Não precisa adicionar a barra invertida. Eu só
coloquei alí para que fique legível em telas
pequenas 😬

As dependência devem estar sendo baixadas, e
os arquivos `composer.json` e `composer.lock`
devem ter aparecido no seu diretório local.
Ah, e tem uma pasta `vendor/` também.

Parece que rolou...

Bora então cirar um serviço php simplão pra rodar
coisa de cli. A gente vai usar a imagem oficial
do php para cli pra isso. E quanto mais chique
melhor, vamo fazer com o php 7.4! 🔥

## Preparando uma cli PHP

Vamos usar a imagem `php:7.4-cli` pra isso.

Vamos também mapear os volumes da mesma forma
que fizemos com o composer. Pode ser útil no
futuro.

```yaml
# docker-compose.yml
version: '3'
services:
  composer:
    image: composer:1.9.3
    environment:
      - COMPOSER_CACHE_DIR=/app/.cache/composer
    volumes:
      - .:/app
    restart: never
  # NOVO AQUI
  php:
    image: php:7.4-cli
    restart: never
    volumes:
      - .:/app
    working_dir: /app
```

Aqui a gente também colocou o working dir
com o valor `/app`. Então sempre que rodarmos
`dcr php` ele irá executar como se `/app` fosse
o caminho inicial de execução.

Tá pensando como vamos rodar os testes, certo?

Siligaaqui!

## Rodando PHPUnit dentro do container

Rodar PHPUnit deveria ser tão simples quanto
rodar um comando de cli. Já que ele é um comando
de cli...

O seguinte, portanto, funciona bem:

```bash
$ dcr php vendor/bin/phpunit
```

Você pode usar <TAB\> para auto completar
normalmente 😉

Parece bem chatão escrever tudo isso aí cada
vez mais. Dá pra simplificar?

Sim!

Vamos adicionar um serviço `phpunit` para o
nosso `docker-compose.yml`:

```yaml
# docker-compose.yml
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
  # NOVO AQUI
  phpunit:
    image: php:7.4-cli
    restart: never
    volumes:
      - .:/app
    working_dir: /app
    entrypoint: vendor/bin/phpunit
```

O truque aqui tá no `entrypoint`!
Agora em seu terminal você pode executar
o seguinte:

```
$ dcr phpunit --version
PHPUnit 9.0.1 by Sebastian
Bergmann and contributors.
```

Aooo! Que lindeza!

A gente, aliás, gerar o nosso `phpunit.xml`
antes de pular pro próximo passo.

Assim ó:

```bash
$ dcr phpunit \
  --generate-configuration
```

Esse comando vai te perguntar algumas coisas.
Apenas pressione enter pra tudo e tá de boa...

## Criando um teste simples

Só pra ter certeza de que as coisas tão
rodando né.

```bash
$ touch tests/MyTest.php
```

E dentro de `tests/MyTest.php` adicione o
seguinte:

```php
# tests/MyTest.php
<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class MyTest extends TestCase
{
  public function testMyTest(): void
  {
    self::assertTrue(false);
  }
}
```

Funciona perfeitamente! E o teste também está
falhando... Tu pode consertar depois, relaxe!

Agora que conseguimos rodar os nossos testes,
podemos pensar em construir a aplicação em si.

Provavelmente você quer criar uma aplicação web,
sim? Então vamos fazer algo com o nginx e php-fpm!!

## Configurando o Web Server

Para configurar o php fpm, precisaremos de dois
serviços diferentes. Um será o servidor HTTP e
o outro será a instância FPM.

Como estes são processos de longa execução, a
gente não vai usar o `docker-compose run` com eles.
Em vez disso, usemos o `up -d`.

O comando final vai parecer com o seguinte:

```bash
$ docker-compose up -d fpm nginx
```

Vamos adicionar o PHP-FPM na bagaça:

```yaml
# docker-compose.yml
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
  phpunit:
    image: php:7.4-cli
    restart: never
    volumes:
      - .:/app
    working_dir: /app
    entrypoint: vendor/bin/phpunit
  # NOVO AQUI
  fpm:
    image: php:7.4-fpm
    restart: always
    volumes:
      - .:/app
    
```

Simplasso! Ao rodar `docker-compose up -d fpm` ele
deveria rodar e ficar no background já.

Agora vamos configurar a parte do nginx que vai
expor a porta `8080` e tratar as requests ao
php envinando para a porta `9000` do fpm.

O arquivo docker-compose.yml vai ficar assim:

```yaml
# docker-compose.yml
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
  phpunit:
    image: php:7.4-cli
    restart: never
    volumes:
      - .:/app
    working_dir: /app
    entrypoint: vendor/bin/phpunit
  fpm:
    image: php:7.4-fpm
    restart: always
    volumes:
      - .:/app
  # NOVO AQUI
  nginx:
    image: nginx:1.17.8-alpine
    ports:
      - 8080:80
    volumes:
      - .:/app
      - ./var/log/nginx:/var/log/nginx
      - .conf/nginx/site.conf:/etc/nginx/conf.d/default.conf

```

Com isto nós expomos a porta `8080` como sendo
a porta `80` do container (porta padrão do http).

Também ligamos o nosso diretório atual para `/app`.
Normalmente as pessoas fazem `/var/www`, mas eu
gosto de deixar as coisas consistentes em comparação
com os outros serviços.

O diretório local `var/log/nginx` foi conectado ao
`/var/log/nginx` do container. Desta forma a gente
não fica cego quando precisar checar os logs de
acesso ou erros.

Por último, mas não menos importante, o `site.conf`
foi introduzido ao container com o nome `default.conf`.
Esta é só uma maneira rápida de fazer com que o nginx
aceite a nossa configuração.

A gente precisa criar o nosso arquivo de configuração.
Façamos então!

```bash
$ touch .conf/nginx/site.conf
```

Escreva o seguinte arquivo de configuração
no caminho `.conf/nginx/site.conf` file:

### @TODO

```conf
# .conf/nginx/site.conf
server {
  listen 80;
  listen [::]:80;

  root /app/public;
  index index.php;

  location / {
      try_files $uri $uri/ /index.php$is_args$args;
  }

  location ~ .php$ {
      try_files $uri =404;
      fastcgi_split_path_info ^(.+.php)(/.+)$;
      fastcgi_pass fpm:9000;
      fastcgi_index index.php;
      include fastcgi_params;
      fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
      fastcgi_param PATH_INFO $fastcgi_path_info;
  }

  access_log /var/log/nginx/myapp_access.log;
  error_log /var/log/nginx/myapp_error.log;
}
```

Notice that `root` is set to `/app/public`.
This will be the entry path to every request
nginx handles.

Also look at the `fastcgi_pass` and see that
it points to `fpm:9000`. **This is our fpm image.
If you named it differently, adjust this line
as well!**

To test this out, let's create a simple
`index.php` inside our `/public` folder.
This file will serve as our application's
entry point.

Just add a simple phpinfo call to this file:

```php
# public/index.php
<?php

phpinfo();

```

Now just lift the nginx server:

```bash
$ docker-compose up -d nginx
```

From this moment on you should be able to
enter http://localhost:8080/ from your
browser normally.

## Don't forget the autoloader

We installed composer properly, but
using our own classes is still not
optimal.

Let's adjust our composer.json file
so composer knows from where to
autoload stuff:

```json
# composer.json
{
  "require-dev": {
    "phpunit/phpunit": "^9.0"
  },
  "autoload": {
    "psr-4": {
      "ThePHPWebsite\\": "src/"
    }
  }
}

```

Now just run a composer dump:

```bash
$ dcr composer -- dump
Generated autoload files
containing 646 classes
```

This `--` before the actual command just
makes sure that `docker-compose` won't
think `dump` is a service instead of a
parameter to our command.

To test this, let's create a file named
`App.php` inside `src/`:

```php
# src/App.php
<?php

declare(strict_types=1);

namespace ThePHPWebsite;

class App
{
  public function sayHello(): void
  {
    echo 'Hello!';
  }
}
```

And now just modify the `public/index.php`
in order to use our new App class:

```php
<?php

require_once __DIR__
  . '/../vendor/autoload.php';

use ThePHPWebsite\App;

$app = new App();

$app->sayHello();

```

Refresh your browser tab and we shall
see a "Hello!" message on the screen!

---

Yeah, that's it! An awesome guide on setting
up a local development environment for
PHP very quickly using docker compose and enabled
to run tests with phpunit.

You might want to add some other things as well,
like databases, queues or maybe a Solr server...

You're free to evolve without messing up your whole
computer/server/repository.

If at some point you find that you'll need
specific things like a certain php extension or
something fancy about your containers, just
create your custom Docker and replace in your
docker-compose.yml file.

Don't forget to share with your lazy friends
whenever they start crying about a skeleton set up
for PHP and let me know if you faced any trouble
during this tutorial.

Cheers!

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
  "headline": "Set up rápido do PHP, PHPUnit e Docker",
  "description": "Nest post eu mostro rapidinho meu setup para aplicações php usando PHPUnit e Docker e algumas configs rápidas que quase todo app precisa.",
  "image": [
    "{{ $page->getBaseUrl() }}/assets/images/posts/7-container-640.webp"
   ],
  "datePublished": "2020-02-17T00:00:00+08:00",
  "dateModified": "2020-02-17T00:00:00+08:00",
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
