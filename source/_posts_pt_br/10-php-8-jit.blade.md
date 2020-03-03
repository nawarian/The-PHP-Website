---
lang: pt-br
slug: php-8-jit
title: Just In Time Compiler e o PHP 8
category: walkthrough
createdAt: 2020-03-03
sitemap:
  lastModified: 2020-03-03
image:
  url: /assets/images/posts/10-php-8-jit-640.webp
  alt: 'Um número oito representado por uma correia de motor.'
tags:
  - core
  - curiosidade
  - php8
  - versão
meta:
  description:
    O Just In Time compiler do PHP 8 foi implementado como parte
    da extensão Opcache e pretende compilar alguns Opcodes em
    instruções de CPU em tempo de execução. Bora entender como
    isso funciona por baixo dos panos.
  twitter:
    card: summary
    site: '@nawarian'
---

[Read in English](/en/issue/php-8-jit/)


## TL;DR

PHP 8’s Just In Time compiler is implemented as part
of the [Opcache extension](https://www.php.net/manual/en/book.opcache.php)
and aims to compile some Opcodes into CPU instructions
in runtime.

Significa que **com o JIT alguns Opcodes não precisarão
ser interpretados pela Zend VM e estas instruções serão
executadas diretamente a nível de CPU.**

## JIT e PHP

Uma das novidades mais comentadas sobre o PHP 8 é
o Just In Time (JIT) compiler. Vários blogs e pessoas
da comunidade estão falando sobre isso e com certeza
é um dos tópicos mais relevantes desta versão. Porém
até o momento eu não consegui achar muitos detalhes
sobre o que o JIT realmente faz.

Depois de pesquisar e desistir várias vezes, eu decidi
verificar o código fonte do PHP por conta. Alinhando
meu pouco conhecimento na linguagem C e toda informação
espalhada que encontrei até o momento, eu compilei esta
publicação e espero que lhe ajude a entender o JIT melhor
também.

**Ultra simplificando: quando o JIT funciona como esperado,
seu código não será executado através da Zend VM e sim
diretamente a nível de instruções de CPU.**

Essa é a ideia.

Mas pra entender melhor a gente precisa pensar sobre como
o PHP funciona internamente. Não é muito complicado, mas
precisa de uma certa introdução ao assunto.

Eu escrevi um post com uma [visão ampla sobre como o php funciona](/br/edicao/como-php-funciona-na-verdade)
. Se você perceber que este post aqui está ficando denso
demais, verifique este outro e volta aqui mais tarde. As
coisas farão sentido mais facilmente.

## Como um código PHP é executado?

Sabemos que o php é uma linguagem interpretada. Mas o
que isso realmente quer dizer?

Sempre que você quiser executar um código PHP, sendo este
um snippet ou uma aplicação web inteira, você precisará
passar por um interpretador php. Os mais comumente utilizados
são o PHP FPM e o interpretador de linha de comando.

O trabalho destes interpretadores é bem direto: receber um
código php, interpretar este código e cuspir o resultado.

Isto normalmente acontece em toda linguagem interpretada.
Algumas podem remover alguns passos, mas a ideia geral é
a mesma. No PHP funciona assim:

1. O código PHP é lido e transformado em uma série de
palavras chave conhecidas como Tokens. Este processo
permite que o interpretador possa entender que parte de
código está escrito em qual parte do programa. **Este
primeiro passo é chamado de Lexing ou Tokenizing.**

1. Com os tokens em mãos, o interpretador PHP analisa
esta coleção de tokens e tenta tomar algum sentido deles.
Como resultado uma Árvore de Sintaxe Abstrata (Abstract
Syntax Tree, ou AST) é gerada através de um processo
chamado **parsing**.
Esta AST é uma série de nós (ou nodos) indicando quais
operações deverão ser executadas. Por exemplo, "echo  1 + 1"
deveria de fato significar "apresente o resultado de 1 + 1"
ou de forma mais realista "apresente uma operação, a operação
é 1 + 1".

1. Em posse do AST fica muito mais fácil entender as
operações e suas precedências. Transformar esta árvore
em algo que possa ser executado requer uma representação
intermediária (Intermediate Representation, IR) que em PHP
chamamos de Opcode. O processo de transformar a AST
em Opcodes é chamada de **compilação**.

1. Agora, com os Opcodes em mãos vem a parte massa:
**execução** do código! O PHP tem um motor chamado
Zend VM, que é capaz de receber uma lista de Opcodes
e executá-la. Após executar todos os Opcodes, a Zend VM
encerra a execução e o programa é terminado.

Eu montei um diagrama de fluxo pra tentar deixar
um pouco mais claro pra ti:

<figure style="text-align: center">
  <a href="/assets/images/posts/10-php-8-jit/zendvm-no-opcache.png" target="_blank">
    <img src="/assets/images/posts/10-php-8-jit/zendvm-no-opcache.png" alt="Fluxo de interpretação do PHP." />
  </a>
  <figcaption>Uma visão simplificada sobre como o PHP é interpretado.</figcaption>
</figure>

Diretão, como tu pode reparar. Mas tem um gargalo aqui:
pra quê fazer o lexing e parsing do código a cada vez
que formos executar um script se o próprio código PHP
não muda com frequência?

No fim das contas a gente só se importa com os Opcodes,
certo? Certo! E é por isso que a **extensão Opcache**
existe.

## A extensão Opcache

A extensão Opcache é compilada com o PHP e normalmente
não há motivos pra desativá-la. Se você usa PHP, você
provavelmente deveria mantê-la ativa.

O que essa extensão faz é adicionar uma camada de cache
em memória para os Opcodes. Sua função é pegar os Opcodes
recém gerados através da AST e jogá-los num cache para que
as próximas execuções possam facilmente pular as fases
de Lexing e Parsing.

Aqui vai outro diagrama, desta vez considerando a
extensão Opcache:

<figure style="text-align: center">
  <a href="/assets/images/posts/10-php-8-jit/zendvm-opcache.png" target="_blank">
    <img src="/assets/images/posts/10-php-8-jit/zendvm-opcache.png" alt="Fluxo de interpretação do PHP com Opcache." />
  </a>
  <figcaption>Fluxo de interpretação do PHP com Opcache. Se um arquivo já foi interpretado, o php busca o Opcode em cache em vez de realizar o parsing novamente.</figcaption>
</figure>

Lindo ver como ele pula os passos de Lexing, Parsing e Compiling 😍.

**Nota:** aqui é justamente onde
[a função de preloading do PHP 7.4](https://wiki.php.net/rfc/preload)
brilha! Ela permite que você diga ao PHP FPM pra
fazer o parsing do seu código fonte, transformá-lo
em Opcodes e jogar no cache antes mesmo de executar
qualquer código seu.

Você deve estar se perguntando onde o JIT entra
nessa história, né?! Bom, espero que sim, é o motivo
de eu ter gastado tanto tempo nesse texto no fim
das contas...

## O que o Just In Time compiler faz efetivamente?

Após escutar a explicação do Zeev no [episódio PHP and JIT do PHP Internals News](https://phpinternals.news/7)
eu consegui ter alguma ideia sobre o que o JIT
deveria fazer...

Se o Opcache faz com que a obtenção de Opcodes
seja mais rápida para que possam ir direto para
a Zend VM, o JIT faz com que eles executem sem
Zend VM nenhuma.

A Zend VM é um programa escrito em C que haje
como uma camada entre Opcodes e a CPU. **O que
o JIT faz é gerar código compilado em tempo de
execução para que o php possa pular a Zend VM e
executar diretamente na CPU.**
Teóricamente a gente deveria ganhar em performance
com isso.

Isto me soou estranho num primeiro momento, porque
pra compilar código de máquina é preciso escrever
uma implementação beeem específica para cada tipo
de arquitetura. Mas na realidade é bem plausível.

A implementação do JIT em PHP usa uma biblioteca
chamada [DynASM (Dynamic Assembler)](https://luajit.org/dynasm.html),
que mapeia uma série de instruções de CPU de um
formato específico em código assembly para vários
tipos diferentes de CPU. Então o Just In Time compiler
transforma Opcodes em código de máquina específico
da arquitetura da CPU usando DynASM.

Mas tem uma coisa me deixou encafifado por um tempão...

**Se o preloading é capaz de transformar PHP em Opcode
antes de executar qualquer coisa e o DynASM pode compilar
Opcodes em código de máquina (compilação Just In Time),
por quê raios a gente não compila PHP em código de máquina
usando a clássica Ahead of Time compilation?!**

Uma das pistas que eu tive ao escutar o episódio
do Zeev é que o PHP é fracamente tipado e, portanto,
o PHP com frequência não sabe qual o tipo de uma
certa variável até que a Zend VM tente executar
um Opcode nela.

Isto pode ser percebido ao olhar para o [union type zend_value](https://github.com/php/php-src/blob/43443857b74503246ee4ca25859b302ed0ebc078/Zend/zend_types.h#L282-L300)
, que possui vários ponteiros de diferentes
representações para uma variável. Sempre que a Zend
VM tenta obter um valor de um zend_value, ela utiliza
macros como a [ZSTR_VAL](https://github.com/php/php-src/blob/43443857b74503246ee4ca25859b302ed0ebc078/Zend/zend_types.h#L794)
que tenta acessar o ponteiro de string através do
union zend_value.

Por exemplo, [este handler da Zend VM](https://github.com/php/php-src/blob/43443857b74503246ee4ca25859b302ed0ebc078/Zend/zend_vm_def.h#L722-L767)
deveria tratar uma expressão de "Menor ou Igual" (<=).
Repare bem em como existe uma porrada de if conditions
pra tentar adivinhar os tipos dos operandos.

**Duplicar esta lógica de inferência de tipos com
código de máquina não é uma tarefa trivial e
potencialmente tornaria a execução mais lenta.**

Compilar tudo depois de entender os tipos também
não é a melhor opção, porque compilar algo para
código de máquina requer muita CPU. Então compilar
TUDO em tempo de execução também é ruim.

## Como o Just In Time compiler se comporta?

Agora sabemos que não podemos inferir tipos
para gerar uma compilação Ahead of Time boa o
suficiente. Também sabemos que compilar em
tempo de execução é custoso. Como pode então o
JIT ser benéfico para o PHP?

Para balancear esta equação, o JIT tenta compilar
apenas alguns Opcodes que ele considera que o
esforço valerá a pena. Para tal, **o JIT faz um
profiling dos Opcodes executados pela Zend VM e
verifica quais fazem sentido ou não compilar.
(baseado em suas configurações)**

Quando determinado Opcode é compilado, ele então
delega a execução a este código compilado em
vez de delegar para a Zend VM. Se parece com o
seguinte:

<figure style="text-align: center">
  <a href="/assets/images/posts/10-php-8-jit/zendvm-opcache-jit.png" target="_blank">
    <img src="/assets/images/posts/10-php-8-jit/zendvm-opcache-jit.png" alt="Fluxo de interpretação do PHP com JIT." />
  </a>
  <figcaption>Fluxo de interpretação do PHP com JIT. Se compilado, Opcodes não executam através da Zend VM.</figcaption>
</figure>

Então na extensão Opcache existem algumas
instruções tentando detectar se determinados
Opcodes deveriam ser compilados ou não. Caso sim,
o compilador então transforma este Opcode em
código de máquina utilizando DynASM e executa
este código de máquina recém gerado.

A coisa interessante nisso tudo é que existe
um limite em megabytes para o código compilado
nesta implementação (também configurável), e
a execução de código deve ser capaz de alternar
entre JIT e código interpretado sem diferença
alguma.

A propósito, [esta palestra do Benoit Jacquemont sobre JIT no PHP](https://afup.org/talks/3015-php-8-et-just-in-time-compilation)
me ajudous demaaais a entender essa coisa toda.

Eu ainda não tenho muita certeza sobre quando
a compilação efetivamente acontece, mas penso
que por agora eu não quero saber, não.

## Então provavelmente os ganhos em performance não serão enormes

Eu espero que agora esteja um tanto mais claro
o motivo de todo mundo dizer que a maioria das
aplicações PHP não receberem grandes melhorias em
performance usando o Just In Time compiler. E o
o motivo de o Zeev ter recomendado fazer
experimentações com diferentes configurações de
JIT em suas aplicações PHP.

Os Opcodes compilados serão normalmente compartilhados
entre várias requests se você estiver utilizando o
PHP FPM, mas isto ainda não é grande coisa.

O motivo é que o JIT otimiza operações de CPU, e
a maior parte das aplicações PHP hoje em dia são
mais forcadas em operações de E/S (I/O) do que
qualquer coisa. Não importa se o processamento
das operações for compilado se você precisar
acessar disco ou rede de qualquer forma. Os
tempos de execução serão bem similares.

**A não ser que...**

Você esteja fazendo algo que não envolve E/S,
como processamento de imagens ou machine learning.
Qualquer coisa que não toque I/O irá se beneficiar
do Just In Time compiler.

Esta também é a razão de algumas pessoas citarem
que agora estamos mais próximos de poder escrever
funções PHP nativas, escritas em PHP em vez de C.
O peso adicional não será custoso se estas funções
forem compiladas.

Tempos interessantes para ser um(a) programador(a) PHP...

---

Eu espero que este artigo lhe tenha sido útil e
que você tenha conseguido entender melhor o que
o JIT do PHP 8 faz.

Sinta-se convidado(a) a me escrever no twitter se
você gostaria de adicionar alguma coisa que eu possa
ter esquecido e não se esqueça de compartilhar com
seus(uas) coleguinhas programadores(as), isto com
total certeza irá adicionar muito valor à conversa
de vocês!

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
  "headline": "Just In Time Compiler e o PHP 8",
  "description": "O Just In Time compiler do PHP 8 foi implementado como parte da extensão Opcache e pretende compilar alguns Opcodes em instruções de CPU em tempo de execução. Bora entender como isso funciona por baixo dos panos.",
  "image": [
    "{{ $page->getBaseUrl() }}/assets/images/posts/10-php-8-jit-640.webp"
   ],
  "datePublished": "2020-03-03T00:00:00+08:00",
  "dateModified": "2020-03-03T00:00:00+08:00",
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
