---
slug: tdl-aprendizado-guiado-por-testes
lang: pt-br
title: TDL, um framework de aprendizado (de linguagens de programação)
category: guides
createdAt: 2020-02-01
sitemap:
  lastModified: 2020-02-01
image:
  url: /assets/images/posts/6-tdl-framework-640.webp
  alt: 'Uma caixa entreaberta'
tags:
  - testes
  - aprendizado
  - linguagens
  - rust
meta:
  description:
    Aprender uma nova linguagem (de programação) é uma habilidade
    extremamente necessária para qualquer engenheiro(a) fullstack.
    Sendo eu mesmo um fullstack, como a maioria dos(as) programadores(as)
    PHP, eu desenvolvi meu próprio framework para racionalizar este
    aprendizado.
  twitter:
    card: summary
    site: '@nawarian'
---

[Read in English](/en/issue/tdl-test-driven-learning-framework/)

## TL;DR

TDL = Test-Driven Learning OU Aprendizado Guiado por Testes.

Se você pudesse levar uma única coisa deste texto contigo, leva
isso aqui: **Desenvolvimento guiado por testes pode ser utilizado
para aprender também.** [Aprenda como fazer TDD de forma eficiente](/br/edicao/tdd-com-php-na-vida-real/)
e você entenderá rapidinho o conceito deste texto inteiro.

<hr>

Recentemente eu inciei (novamente) a missão mais divertida que
nós programadores(as) enfrentamos de tempos em tempos:
**✨ aprender uma nova linguagem de programação ✨.**

Desta vez eu decidi aprender [Rust](https://www.rust-lang.org/),
que pra mim (ainda) é insanamente interessante, já que sou um
desenvolvedor web e nunca precisei encostar em nada relacionado
a baixo nível que não fosse o código fonte do PHP.

Depois de passar por este processo várias vezes, eu comecei a ver
alguns padrões no meu processo de aprendizado. Foi preciso uma boa
mistura de auto conhecimento, habilidades de gerenciamento de projetos (sim)
e pesquisa pra compilar estes padrões num modelo que eu poderia facilmente
integrar no meu dia a dia.

Eu acredito que desta vez, aprendendo Rust, eu alcancei uma versão
mais ou menos estável deste framework de aprendizado baseado no TDL.
Mesmo que otimizar este framework tenha de ser um processo em constante
evolução.

Pra ser muito claro, **eu não quero dizer que esta é a melhor
forma de aprender uma nova linguagem de programação ou coisa do tipo.**
Você vai precisar aprender por si mesmo qual formato funciona melhor
pra ti.

Como sempre, vamos parar com a enrolação e começar!

<hr>

Cada seção aqui começa com uma lista de tópicos para que você
possa saber as principais ideias antes de ler algo. Sinta-se
livre pra pular para o próximo subtítulo se os tópicos não
chamarem sua atenção.

Este texto está dividido em três partes e, honestamente, daria pra
escrever um livro se eu fosse desenvolver meus pensamentos em cada
uma delas. ([Me dá um alô no twitter se você quiser ver este livro virar realidade xD](https://twitter.com/intent/tweet?text=Escreve+o+livro,+@nawarian,+macho,+v%C3%A9i!+-+https://thephp.website))

1. "**Iniciando com uma nova linguagem de programação**" apresenta um modelo
mental sobre como descobrir uma nova linguagem de programação e como adquirir
a mais fundamental ferramenta de aprendizado com TDL: testes.

2. "**Como funciona o Aprendizado Guiado por Testes?**" tenta explicar como
esse arcabouço funciona, como manter o feedback loop e por aí vai.

3. Por último, mas não menos importante, vem "**Praxis: a parte mais difícil
é a descoberta**", que traz algumas dicas de como alimentar o primeiro passo
do loop do TDL.

## Iniciando com uma nova linguagem de programação

Tópicos nesta seção:

* As linguagens de programação são semelhantes
* Toda linguagem tem um propósito
* Encontrando os pontos altos da linguagem
* Como testar nesta linguagem

Se você já sabe qual linguagem de programação quer aprender, o
processo de inicialização é razoavelmente simples. Mas eu tenho um
conselho bem estranho pra você:

**Não escreva código algum até você ter um modelo mental claro sobre
o que esta linguagem é capaz de fazer.**

Para construir este modelo mental é sempre bom lembrar que linguagens
de programação são muito semelhantes entre si e normalmente conseguem
fazer alguma(s) coisa(s) muito bem (são especializadas).

### As linguagens de programação são semelhantes

**A maioria das linguagens de programação são semelhantes entre si.**
Elas podem até ter diferentes estruturas, sintaxe, convenções mas no
fim das contas a maioria delas vai te prover uma execução baseada em
pilha, onde você **guarda variáveis, carrega valores nelas, executa
expressões, chama funções e por aí vai.**

Imperativa ou declarativa, compilada ou interpretada, **todas elas
compartilham características comuns.** Quanto mais cedo você entender
estas características, mais próximo de ser apenas uma questão de sintaxe
elas vão te parecer.

Além da sintaxe você encontrará modelos que são profundamente ligados
à linguagem. Normalmente eles começam a aparecer quando você começa a
aprender o problema que determinada linguagem resolve.

Então **não comece a codar ainda!** Antes de escrever qualquer linha
de código, tenha certeza de que você olhou bem para a linguagem, sabe
que cara tem e quais coisas tornam esta linguagem única e útil.

Normalmente **uma linguagem se destaca das outras por ter um propósito.**

### Toda linguagem tem um propósito

[PHP era um monte de scripts CGI pra fazer templates](/br/edicao/como-php-funciona-na-verdade/),
o JavaScript nasceu para tornar páginas web interativas, ActionScript
foi para fazer o mesmo com Flash, C para abstrair com alta performance
em desenvolvimento de sistemas, Rust para fazer o mesmo que C com segurança
em mente, Ruby para escrever código como escrevemos livros, e a IR da LLVM
para tornar mais fácil criar novas linguagens de programação...


Toda linguagem de programação tem (ou teve) um propósito quando foi
escrita pela primeira vez.

**Saber o propósito de uma linguagem lhe ajuda a entender como ela
funciona.**

Quando você entende o propósito da linguagem, as melhores funcionalidades
desta linguagem começam a aparecer pra ti naturalmente.

### Encontrando os pontos altos da linguagem

Vamos tomar Rust como exemplo aqui, já que é minha memória mais
recente.

Eu entendi que Rust é uma linguagem de desenvolvimento de sistemas,
com alto foco em segurança no gerenciamento de memória sem perder
em performance. Mas como isso tudo se traduz na linguagem?

Aarre! O compilador reclama de tudo, as variáveis têm ciclo de vida e
ownership, tem uma parada de emprestar variáveis, tipos de dado que
nunca ficam nulos, eu já falei do compilador?

Saber tais coisas vai te ensinar do que tu pode se orgulhar em saber
quando souber, mas também vai te dar uma clara visão de onde você
precisa chegar.

Isso também vai fazer com que outras pessoas acreditem que você está
realmente aprendendo esta linguagem. Não tem nada mais frustrante que
receber um "por que?" sem conseguir responder de maneira satisfatória.

### Como testar nesta linguagem

Ainda **antes de codar qualquer coisa** nessa linguagem, o último
passo para construir este modelo mental é aprender como escrever
testes.

Sim! Mesmo antes de aprender como declarar uma variável, aprenda como
testar. Ou melhor dizendo, **aprenda como fazer asserções**.

PHP e C, por exemplo, possuem a função `assert()`. Rust tem as macros
`assert_*` e uma ferramenta integrada de testes.

Aprenda o que está disponível para fazer testes: tem alguma forma de
escrever testes unitários? De integração? Consigo usar uma linguagem
de testes como o Gherkin com ela?

Eu vou usar um programa em rust como exemplo aqui em como escrever
uma asserção essencial para que a gente possa iniciar nosso loop com TDL:

```rust
// src/main.rs

#[test]
fn test_basics() {
  assert_eq!(1, 1); // 1 = 1
  assert!(true); // sucesso
  assert!(false); // falha
}
```

Depois eu só executo `$ cargo test` e PRONTINHO! Eu sei o básico em
como escrever asserções nessa linguagem nova. Hora de começar a aprender!

<hr>

## Como funciona o Aprendizado Guiado por Testes?

Tópicos nessa seção:

* Introdução com exemplos de código
* Descoberta
* Asserção
* Aprendizado
* Repetição

Pode ser que você pense que eu tô tentando criar moda ou
algo do gênero. Mas [Aprendizado Guiado por Testes é real total de verdadinha](https://digitalcommons.calpoly.edu/csse_fac/88/).

É importante notar que TDL nesta pesquisa acima é apenas uma ferramenta
de ensino. O que eu estou apresentando aqui é como eu uso esta ferramenta
num framework que eu construí e adoto.

Eu baseio meu aprendizado com TDL em três passos repetíveis:
**descoberta, asserção e aprendizado**.

Ferramentas como Khan Academy, Vim Adventures e outros MOOCs também
usam métodos semelhantes a este: apresentar algo novo (descoberta),
dar feedback em suas falhas (asserção) e premiar seus acertos (aprendizado).

A ideia é primeiro **descobrir** algo que você quer ou precisa aprender,
realizar **asserções** para entender quando você terá aprendido. Fazer
as asserções funcionarem com o que você **aprendeu** ou precisa aprender.

Vamos tomar um exemplo bem tosquinho com rust. Eu descobri que posso
definir variáveis. Como isto funciona? Vamos fazer uma asserção de que
a variável `nawarian` deveria conter o valor 10.

```rust
// src/main.rs

#[test]
fn test_variables() {
  assert_eq!(10, nawarian);
}
```

Eu sei agora que eu só vou tentar descobrir novas coisas quando esta
asserção passar. Eu vou continuar brigando com meu teclado até que
`cargo test` fique verde!!

Um pouco de pesquisa aqui e acolá, e o seguinte parece funcionar:

```rust
// src/main.rs

#[test]
fn test_variables() {
  let nawarian = 10;

  assert_eq!(10, nawarian);
} 
```

Os testes estão verdes. Eu poderia continuar com coisas novas ou
brincar mais um pouco com este aqui. Em vez de definir `nawarian = 10`
eu poderia fazer isso com um loop e contador, não?

Então como será que eu posso fazer um loop de 0 a 10 e incrementar
**nawarian** a cada iteração?

```rust
// src/main.rs

#[test]
fn test_variables() {
  let nawarian = 0;
  for i in 0..10 {
    nawarian += 1;
  }

  assert_eq!(10, nawarian);
}
```

Agora o `cargo test` ficou loucão e começou a reclamar de uma par
de coisas:

> **warning:** unused variable: `i`
>
> **help:** consider prefixing with an underscore: `_i`
>
> **error\[E0384]:** cannot assign twice to immutable variable `nawarian`
>
> **help:** make this binding mutable: `mut nawarian`

Não tá funcionando... o compilador diz que `nawarian` não é mutável e
tá implicando até que `i` nunca é utilizada, que eu deveria prefixar
essa variável com `_` pra que ninguém ligue pra ela.

> **Eu:** Diabé!? Aparentemente variáveis são imutáveis por padrão em Rust.
> Interessante... Dá pra fazer ela ficar mutável usando a palavra chave `mut`...

Escreve, roda. FUNCIONOU!

**Essa estrutura é difudê porque você entende exatamente onde está
pisando.** E quando não entender, é mais fácil pesquisar um problema
pequenino e específico que você esteja enfrentando no momento.

Este feedback loop vai te forçar a **aprender ativamente** em vez de
passivamente: encontre uma funcionalidade, teste, encontre a resposta
até ficar satisfeito(a). Descubra, afirme, aprenda.

Outra coisa maravilhosa é que testes passando deixam as pessoas felizes.
Ver o quanto você consegue refatorar um código sem quebrar traz um
sentimento muuuito bom!

Eu não quero dizer que você deveria, mas com essa asserção simples,
você pode aprender desde variáveis até threading. Um passo de cada vez,
mantenha os testes passando e alimente seu feedback loop!

Eu vou tentar te dar um pouco mais de informação em como lidar com cada
passo enquanto utiliza o TDL:

### Descoberta

O passo da descoberta é muito bom para aprender sobre sintaxe, funcionalidades
da linguagem, frameworks... Basicamente tudo o que possa captar seu
interesse se encaixa aqui.

Mas sempre mantenha suas asserções simples. **É extremamente importante que
você entenda seus testes** muito mais que a implementação em si. Os testes
precisam ser claros, o código você pode sempre refatorar.

### Asserção

O passo da asserção pode ser um tanto diferente dependendo do nível
que você já tenha alcançado na linguagem.

Novatos (como eu em Rust) devem escrever teste unitários o máximo possível,
usando ferramentas nativas da linguagem como `assert`, `echo` ou mesmo `exit`.

Quanto mais você avança, testes grandes e complexos usando "Given, When, Then"
podem funcionar melhor. Você precisará aprender como utilizar ferramentas
como Gherkin ou algo tão amplo e descritivo quanto em sua linguagem.

### Aprendizado

Faça com que seus testes passem!

Leia os erros que aparecem pra ti, entenda o problema e assim que o
fizer: leia o manual da linguagem, procure em mecanismos diferentes,
pergunte a amigos ou quem puder saber mais.

### Repetição

Volte ao passo da descoberta e encontre algo novo para fazer novas
asserções e aprender.

Ou apenas seja criativo(a). Aquele simples `nawarian = 10` pode ser
transformado em operações de E/S, utilização de structs, chamadas
FFI, threads...

**Não existe código que você não possa tornar mais complicado! 😉**

<hr>

## Praxis: a parte mais difícil é a descoberta

Tópicos nesta seção:

* Introdução sobre comunidade e ferramentas
* Se envolva com a linguagem
* Conecte-se com a comunidade
* Explique algo que você ainda não sabe

A teoria parece ótima, né? (talvez nem mesmo pareça...)

Mas eu sei o quão difícil é no começo entender e tomar proveito
deste framework. Pra mim a parte mais difícil é alimentar este
feedback loop com mais descobertas.

Felizmente a melhor resposta que eu encontrei até o momento para a
maioria das linguagens/frameworks é: Comunidade!

Rust, por exemplo, tem este projeto maravilhoso chamado "[rustlings](https://github.com/rust-lang/rustlings)".
Ele te guia através de testes escritos pela comunidade e te desafia
a fazer com que os códigos compilem (o compilador é realmente chato),
que os testes passem...

[No php tem o PHP School](https://www.phpschool.io/)
que é bem semelhante ao rustlings, mas é altamente extensível e
tem muitos cursos feitos pela comunidade sobre modulos/funcionalidades
específicas.

Mas depois de aprender a sintaxe você precisará de muito mais. Você
precisará ver em quê e como a linguagem está evoluindo.

### Se envolva com a linguagem

Aqui é o momento de assistir palestras, ir a conferências e
assistir pessoas codando por horas na frente das câmeras...

Comece a contribuir com projetos de código aberto também! Esta é
uma forma bem simples de ganhar proficiência na linguagem: contribua,
falhe nos code reviews e conserte seus erros baseado no feedback de
outras pessoas...

### Conecte-se com a comunidade

Escute também podcasts, leia blogs (ou comece um!), entre em reddits.
Você aprenderá mais e mais sobre o que pessoas estão fazendo
com esta linguagem, o que é considerado normal e o que não é.

Encontre grupos de encontro locais, ou crie um você mesmo.
Converse com pessoas de verdade, sobre problemas de verdade.

### Explique algo que você ainda não sabe

Navegue pelo stackoverflow, github issues ou forums do reddit para
encontrar perguntas que você realmente não sabe responder. Encontre-as,
pesquise e responda (ou ao menos tente).

As vezes você será capaz de explicar somente com assertions, as vezes
você precisará de uma visão mais ampla das coisas. Tente os dois!

Em determinado momento você estará navegando por oportunidades de emprego
nesta linguagem e os requisitos estão quase todos preenchidos. Os que
não estiverem, você pode utilizar para alimentar seu feedback loop e
continuar evoluindo.

<hr>

Fico feliz que tenha chegado até aqui, porque não tô tentando te
enrolar. Meu processo de aprendizado até o momento é bem doloroso
mas extremamente útil pra mim.

Também me salva um bom tempo por me guiar através de perguntas pequenas
e respostas que eu precise dar, já que eu não tenho tempo para investir em
aprendizado de novas linguagens como eu costumava ter há alguns anos
atrás ~ suspiros.

Eu espero que este texto tenha sido útil pra ti.

Como sempre, sinta-se livre pra me mandar qualquer feedback através
do twitter.

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
  "headline": "tdl-aprendizado-guiado-por-testes",
  "description": "Aprender uma nova linguagem (de programação) é uma habilidade extremamente necessária para qualquer engenheiro(a) fullstack. Sendo eu mesmo um fullstack, como a maioria dos(as) programadores(as) PHP, eu desenvolvi meu próprio framework para racionalizar este aprendizado.",
  "image": [
    "{{ $page->getBaseUrl() }}/assets/images/6-tdl-framework-640.webp"
   ],
  "datePublished": "2020-02-01T00:00:00+08:00",
  "dateModified": "2020-02-01T00:00:00+08:00",
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

