---
slug: subindo-arquivos-github
lang: pt-br
title: Subindo arquivos no GitHub
category: guides
createdAt: 2020-11-06
sitemap:
  lastModified: 2020-11-06
image:
  url: /assets/images/posts/18-uploading-files-github-640.webp
  alt: 'Uma mulher desenvolvedora de software atrás de um computador com adesivos do mascote do Github'
tags:
  - git
  - github
  - beginners
meta:
  description:
    Subir arquivos para o GitHub é um processo simples que
    envolve o uso de alguns comandos no terminal como git
    status, add, commit, remote entre outros que veremos aqui.
  twitter:
    card: summary
    site: '@nawarian'
---

Subir arquivos para o GitHub é um processo simples que envolve o uso de alguns
comandos no terminal como git status, add, commit, remote entre outros que
veremos aqui.

**Ao fim deste post há também um vídeo pra te auxiliar visualmente a
entender como subir arquivos no github.**

## Preparação

Para esse tutorial você precisará ter o Git instalado em sua
máquina e uma conta no GitHub.

Para instalarmos o Git no windows basta entrar no [site do git scm](https://git-scm.com/)
e fazer o download. No linux e no mac basta colocar o comando abaixo no terminal.

```bash
# No linux
$ sudo apt-get install git

# No mac
$ brew install git
```

## Iniciando seu repositório

Precisaremos criar o repositório no GitHub, faça login na sua conta, no seu
perfil vá em Repositories e New.

<figure style="text-align: center">
  <a href="/assets/images/posts/18-uploading-files-github/01-new-repository.png" target="_blank">
    <img src="/assets/images/posts/18-uploading-files-github/01-new-repository.png" alt="Criando um repositório no Github." />
  </a>
</figure>

Coloque o nome desejado no campo Repository Name e uma breve descrição (não
é obrigatório), no nosso exemplo vamos utilizar o thePHP.

## Configurações iniciais

Antes de iniciarmos um repositório local é necessário definir seu nome e
endereço de e-mail, essa configuração inicial é importante porque todos
os commits do git utilizará essas informações, para isso use os comandos
abaixo no terminal.

```bash
$ git config --global user.name "<nome>"
$ git config --global user.email "<email>"
```

Caso você queira verificar as configurações é só utilizar o comando:

```bash
$ git config user.name
```

Para iniciarmos um repositório git, no terminal execute o comando abaixo
(certifique-se que você está dentro do seu repositório utilizando o comando
cd) ou com o botão direito do mouse dentro da pasta onde ficará o seu
projeto clique em Git Bash Here:

<figure style="text-align: center">
  <a href="/assets/images/posts/18-uploading-files-github/02-git-bash-here.png" target="_blank">
    <img src="/assets/images/posts/18-uploading-files-github/02-git-bash-here.png" alt="Git Bash Here no Windows." />
  </a>
</figure>

E execute o comando:

```bash
$ git init
```

## Git status

O Git utiliza estados para monitorar seus arquivos, com o “git status”
é possível ter um controle do que está se passando em seu repositório,
para entendermos melhor como funciona, temos o exemplo abaixo que criará
um arquivo Hello World.txt contendo dentro dele um simples texto
“hello world” e mostrará o estado do arquivo.

```bash
$ echo Hello World > helloWorld.txt
$ git status
```

> on branch master
No commits yet
Untracket files:
      (use “git add <file>...” to include in what will be commited)
            hellowWorld.txt
nothing add to commit but untracked files present (use “git add” to track);

Podemos observar que o arquivo helloWorld.txt está com o estado de untracked
file, isso quer dizer que o arquivo não está sendo monitorado pelo git,
então qualquer coisa que acontecer com ele o git não será “responsável”.

## Git add

O comando “git add <nome do arquivo>” ou “git add .”  (para todos os arquivos
no repositório) monitora os arquivos e adiciona uma alteração dele no diretório
à staging area, que é o local onde prepara os arquivos para o próximo comite.

```bash
$ git add helloWorld.txt
$ git status
```
> on branch master
No commits yet
Changes to be committed:
         (use “git rm –cached <file>...” to unstage)
                   new file:      helloWorld.txt

## Git commit

O “git commit” captura o estado atual do arquivo e adiciona as alterações
para o histórico do repositório local atribuindo um hash como identificação,
com o “–m” podemos adicionar uma descrição das mudanças realizadas.

```bash
$ git commit –m “<descrição>”
```

Utilizando o comando “git status" podemos observar que os arquivos estão
prontos para serem encaminhados para o diretório remoto.

## Git remote

O “git remote” é utilizado para gerenciar os repositórios monitorados, o
exemplo abaixo registra o repositório remoto e adiciona o endereço em
“origin” onde passaremos o link que pode ser encontrado na aba code do
nosso repositório no GitHub.

```bash
$ git remote add origin <endereço>
```

## Git Push

Com o “git push –u origin <branch>” podemos encaminhar as mudanças
commitadas para o GitHub, nesse exemplo utilizaremos o branch padrão master.

```bash
$ git push -u origin master
```

Depois que recarregar a página do seu repositório remoto você verá seus
arquivos atualizados.

## Conclusão

Vimos que para utilizar uma ferramenta tão poderosa como o git e subir
seus arquivos para um repositório remoto é mais simples do que parece,
com apenas alguns comandos aprendemos a versionar nossos arquivos e
deixá-los em um local acessível e seguro para serem utilizados a
qualquer momento.

Como material de apoio, deixo aqui também o vídeo do Jeterson que vai
te ajudar a visualizar melhor como subir arquivos no github.

<iframe style="margin: auto;" width="560" height="315" src="https://www.youtube.com/embed/O2DFKHla80A" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

<hr>

Esta publicação foi uma contribuição de autoria de João Apostulo Neto.
Você pode entrar em contato com ele em seu Github [@japostulo](https://github.com/japostulo)
ou LinkedIn [/joaoapostulo](https://linkedin.com/in/joaoapostulo).

Envie você também a sua, mande um PR [no repositório aberto do thephp.website](https://github.com/nawarian/The-PHP-Website).

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
  "headline": "Subindo arquivos no GitHub",
  "description": ".Subir arquivos para o GitHub é um processo simples que envolve o uso de alguns comandos no terminal como git status, add, commit, remote entre outros que veremos aqui.",
  "image": [
    "{{ $page->getBaseUrl() }}/assets/images/posts/18-uploading-files-github-640.webp"
   ],
  "datePublished": "2020-11-06T00:00:00+08:00",
  "dateModified": "2020-11-06T00:00:00+08:00",
  "author": {
    "@type": "Person",
    "name": "João Apostulo Neto"
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
