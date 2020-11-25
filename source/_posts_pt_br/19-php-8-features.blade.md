---
isFeatured: true
lang: pt-br
slug: php-8-features
title: 'PHP 8.0 lançado: tá com uma cara ótima!'
category: walkthrough
createdAt: 2020-11-26
sitemap:
  lastModified: 2020-11-26
image:
  url: /assets/images/posts/19-php-features-640.webp
  alt: 'Uma imagem com um elefante gigante e brilhante'
tags:
  - core
  - curiosidades
  - php8
  - lançamento
meta:
  description:
    PHP 8.0 trouxe várias inovações, dentre elas incríveis
    mudanças sintáticas, atualizações nas APIs e mudanças
    fundamentais no core e, claro, várias correções de bug.
    Aqui eu vou te mostrar as principais mudanças à linguagem!
  twitter:
    card: summary
    site: '@nawarian'
---

[Read in English](/en/issue/php-8-features/)

Chegou a hora! No dia 26 de Novembro de 2020, o PHP 8.0
foi lançado e tornado disponível para download. Depois de
5 Release Candidates e um enorme esforço da comunidade,
nós podemos finalmente começar com o PHP 8.0 em produção.

PHP 8.0 trouxe várias inovações, dentre elas incríveis
mudanças sintáticas, atualizações nas APIs e mudanças
fundamentais no core e, claro, várias correções de bug.
Aqui eu vou te mostrar as principais mudanças à linguagem!

## Mudanças Sintáticas do PHP 8.0

Há várias mudanças sintáticas na linguagem nesta versão! Eu
consigo ver claramente uma tendência: o php está tentando
ficar cada vez mais ergonômico quando se trata de operações
rápidas e classes.

Abaixo eu listo 8 mudanças sintáticas que entraram no PHP 8.0
e dou uma introdução rápida. Todas elas terão links para a
RFC que as introduziu à linguagem, então caso você tenha
dúvidas basta ler os links
[(ou abrir uma issue, eu vou responder o quanto antes)](https://github.com/nawarian/The-PHP-Website/issues).

### Union Types e o Mixed Type

Eu escrevi um post detalhado sobre como o sistema de tipos
do PHP se organiza entre escalares, compostos e especiais.
[Você pode acessar o post neste link aqui](/br/edicao/tipos-em-php/).

PHP 8.0 traz duas mudanças importantes que transformaram os
tipos compostos em uma estrutura formal da linguagem em vez
de apenas uma convenção como antes.

[A primeira mudança é a dos Union Types](https://wiki.php.net/rfc/union_types_v2)
, que torna possível definir que tipos uma variável pode ter
e gerará um erro se um tipo de valor inesperado for passado.
A sintaxe é bem semelhante com o que o TypeScript faz:

```php
<?php

function sumTwo(int|float $x): int
{
  return round($x + 2);
}
```

Mas tem algumas limitações. O tipo `void` não pode ser utilizado,
e todas as declarações de tipo não podem ser ambíguas. Por
exemplo `MyClass|object` não deveria compilar, porque `object`
já bate com qualquer instância de qualquer classe.

[A segunda mudança adiciona o tipo _mixed_ no PHP 8.0](https://wiki.php.net/rfc/mixed_type_v2).
O tipo _mixed_ é na verdade um Union Type muito específico.
Você pode pensar nele como um alias do Union `array|bool|callable|int|float|null|object|resource|string`
e deverá funcionar semelhante ao tipo `any` do TypeScript.

### Atributos (Annotations)

Esta mudança foi definitivamente a que trouxe mais discussão
na comunidade PHP. Ao todo foram 5 RFCs para compor esta
alteração de sintaxe, todas muito discutidas pelos internals
e pela comunidade nas redes sociais.

A primeira vez que apareceu foi em 2016,
[com a proposta do Dmitry](https://wiki.php.net/rfc/attributes)
, mas não passou já que a implementação não era suficiente para
substituir as implementações já existentes como do
[Doctrine\Annotations](https://github.com/doctrine/annotations)
ou
[Php-Annotations\Php-Annotations](https://github.com/php-annotations/php-annotations).
Esta RFC foi extremamente importante para construir o conhecimento
para a RFC da nova sintaxe.

[Em março esta RFC foi revivida por Benjamin Eberlei e Martin Schröder](https://wiki.php.net/rfc/attributes_v2)
, corrigindo a maioria dos problemas que a comunidade encontrou
antes. A sintaxe ficou mais ou menos assim:

```php
<?php

use PhpAttribute;

<<PhpAttribute>>
class MyAttributesClass
{
}

<<MyAttributesClass>>
function myFunction () {}

$reflection = new ReflectionFunction('myFunction');
// ReflectionAttribute[]
var_dump($reflection->getAttributes());
```

Os Atributos são referências a classes que podem ser
instanciadas a partir do próprio objeto _ReflectionAttribute_.
Cada classe de Atributo deverá utilizar um atributo do próprio
PHP chamado _PhpAttribute_, isto só mudou depois que a RFC
[attribute amendments](https://wiki.php.net/rfc/attribute_amendments)
passou e agora em vez de _PhpAttribute_ deverá ser
utilizada a classe _Attribute_.

Esta sintaxe pode ser utilizada com:
- funções, closures e short closures
- classes, classes anônimas, interfaces, traits
- constantes de classes
- propriedades de classes
- métodos de classes
- parâmetros de funções ou métodos

Esta segunda RFC (attribute amendments) também trouxe
mudanças interessantes como a validação de que um Atributo
deveria ser utilizado somente com classes ou com funções,
se poderia ser utilizado várias vezes ou não, e também a opção
de agrupar utilizações de atributos.

As duas últimas RFCs (
[esta](https://wiki.php.net/rfc/shorter_attribute_syntax)
e [esta](https://wiki.php.net/rfc/shorter_attribute_syntax_change)
)
foram somente sobre como a sintaxe de utilização dos
Attributes deveria ser. A sintaxe final adotada ficou
como a seguinte e parece bastante com a do Rust:

```php
<?php

use Attribute;

#[Attribute(Attribute::TARGET_FUNCTION)]
class MyAttributesClass
{
}

#[MyAttributesClass]
function myFunction () {}

$reflection = new ReflectionFunction('myFunction');
// ReflectionAttribute[]
var_dump($reflection->getAttributes());
```

Reparou naquele `Attribute::TARGET_FUNCTION` alí? Ele diz ao
php que aquele Attribute só pode ser utilizado com funções
e um erro será gerado se utilizado em qualquer outro lugar.

### Operador Nullsafe

_Uncaught Error: Call to a member function example() on null_.
Este erro aqui persegue muitos(as) engenheiros(as) php que podem
ter esquecido de verificar o tipo de retorno ou tenham escrito
errado uma condição if.

Isto vai mudar no PHP 8.0, com a
[introdução do operador nullsafe](https://wiki.php.net/rfc/nullsafe_operator).
Esta sintaxe faz verificações em valores nulos e dá um curto-circuito
se alguma parte da cadeia for null, evitando uncaught errors como
o mencionado acima. A sintaxe é a seguinte:

```php
<?php

$obj = new class {
  public function f()
  {
    return null;
  }
}

// "$obj?->" verifica se $obj é null e,
// caso não, continua coma a chamada
// "f()?->" verifica se o tipo do retorno de f()
// é null como acima
$obj?->f()?->neverCalled();

// neverCalled() nunca foi chamado, pois f() retorna null
```

### Non-Capturing Catches

Sempre que escrevemos o bloco _catch_ quando tratamos
uma exceção é necessário receber também o objeto Exception.

[No PHP 8.0, graças ao Max Semenik](https://wiki.php.net/rfc/non-capturing_catches)
, não será mais necessário fazer isso. Agora é possível
capturar exceções sem precisar capturar o objeto em si.
Como no exemplo abaixo:

```php
<?php

try {
  throw new IncredibleException();
} catch (IncredibleException) {
  // Eu não ligo tanto pro
  // objeto $exceptionabout
  // the $exception object
} catch (Exception $e) {
  // Mas aqui eu ligo, e tudo bem
}
```

### Throw Expression

Anteriormente a palavra chave _throw_ era considerado um
_statement_ na linguagem, o que nos impediu por muito tempo
de lançar exceções em alguns lugares onde apenas expressões
como atribuição de variáveis, short closures, ternários
e expressões binárias poderiam estar.

[Ilija Tovilo implementou a RFC Throw Exception](https://wiki.php.net/rfc/throw_expression)
que transformou `throw $obj` numa expressão. Então os exemplos
abaixo são válidos:

```php
<?php

$a = null ?? throw new Exception();
$b = $obj->func() || throw new Exception();
$c = fn() => throw new Exception();
```

Esta funcionalidade foi inspirada numa mudança introduzida
ao C# em 2017 e uma proposta ao ECMAScript escrita em 2018.

### Expressão Match

Esta é a minha favorita! A intenção é trazer uma sintaxe
mais limpa sempre que normalmente faríamos um _switch_ para
decidir o valor de uma variável.

[A RFC foi escrita por Ilija Tovilo](https://wiki.php.net/rfc/match_expression_v2)
e nesta versão ainda não oferece suporte a blocos, então
apenas expressões são permitidas. A utilização ficou assim:

```php
<?php

$a = 100;

$duzentos = match ($a) {
  10, 100, 1000 => $a * 2,
  50, 500, 5000 => $a / 2,
};
```

O snippet acima retornaria `$a * 2` sempre que $a for igual a 10,
100 ou 1000. Retornaria `$a / 2` sempre que $a for igual a 50,
500 ou 5000.

É importante observer que a sintaxe de Match constrói uma expressão,
então ela pode ser armazenada em variáveis, passada como parâmetro
ou ser composta com outras expressões.

```php
<?php

$type = ...;
$filter = match ($type) {
  'as_object' => $myObject,
  'assoc' => $myObject->toArray(),
} || throw new InvalidArgumentException('Invalid type requested.');
```

Implementações futuras irão adicionar suporte a blocos ao
lado direito desta expressão, de forma semelhante ao que
o Rust faz. Isto dá ao desenvolvedor(a) maior flexibilidade
para escrever programas complexos sem invadir escopos
de variáveis.

### Named Parameters

É bem comum ver métodos com parâmetros contendo valores padrão
e os únicos que queremos mudar são os últimos. Isto nos força
a entrar com `null` em todos os parâmetros para mudar apenas
os últimos.

Muitos podem dizer que isto é um problema de design, mas ao
mesmo tempo não é possível simplesmente garantir um ótimo
design para cada projeto open source escrito por aí.

[Nikita Popov decidiu adicionar o Named Parameters ao PHP 8.0](https://wiki.php.net/rfc/named_params)
, que nos permite pular parâmetros de funções ou métodos e
definir valores somente para as que nos importam. Para isso
as variáveis precisam ser nomeadas, funciona assim:

```php
<?php

function myFunc(
  $a = 10,
  $b = 20,
  $c = null
) {
}

myFunc(c: 100);
// $a = 10; $b = 20; $c = 100
```

Isto também nos dá a liberdade de desconsiderar a ordem dos
parâmetros definida pela interface.

Eu acho que esta é uma ótima forma para criar um código mais
bem escrito sem quebrar bibliotecas e extensões que já existem.

### Constructor Promotion

Alguns dizem que o PHP é tão verboso quanto o Java quando se
trata de Orientação a Objetos. Eu tendo a concordar e creio que
poderíamos importar algumas facilidades de sintaxe que outras
linguagens já construíram e obtiveram sucesso.

[A sintaxe constructor promotion](https://wiki.php.net/rfc/constructor_promotion)
torna mais simples e rápido escrevre classes que recebem
parâmetros no construtor e os joga em propriedades imediatamente.

O snippet abaixo ilustra bem esta nova sintaxe:

```php
<?php

class MyClass
{
  public function __construct(public int $x = 0)
  {}
}

// É equivalente a isto:

class MyClass
{
  private int $x;

  public function __construct(int $x = 0)
  {
    $this->x = $x;
  }
}
```

## Mudanças na Máquina Virtual do PHP 8

Mudanças no core são normalmente as que podem quebrar
nosso código de forma explícita ou silenciosamente, então
é importante tomar uma boa atenção nelas enquanto atualizamos
a versão do PHP.

Esta versão trouxe atualizações muito bacanas ao PHP que impactam
performance e comportamente. Aqui eu vou listar algumas delas.

### Compilador Just In Time (JIT)

[Eu escrevi um artigo sobre o que é o JIT e como ele funciona no PHP](/br/edicao/php-8-jit/).
Recomendo fortemente que você dê uma lida, ele vai te dar
uma ideia melhor sobre como o PHP funciona internamente e
quais benefícios um compilador Just In Time pode trazer
à linguagem.

Resumão do ENEM: o JIT pode aumentar a performance das
nossas aplicações PHP, pode ser otimizado para melhores
resultados e constrói uma fundação para aplicações PHP
diferentes do que estamos acostumados a ver.

Mas isto não vai acontecer em toda aplicação PHP. Há casos
de uso muito específicos para o JIT e eu acho que o
que você pode fazer de melhor é [verificar a RFC](https://wiki.php.net/rfc/jit)
e [ler o post que eu mencionei acima](/br/edicao/php-8-jit/).

Uma coisa interessante sobre esta funcionalidade é que
ela foi implementada antes da RFC de attributes ser
aprovada. Então uma das opções disponíveis é compilar
apenas funções/métodos anotados com um doc-comment `@jit`.
Isto poderá mudar no futuro ao adicionar uma opção nativa
`#[jit]` usando Attributes em vez de doc-comments.

### Weak Maps

O PHP 7.4 nos trouxe uma classe weak-reference (referência fraca),
que nos permite criar uma referência a um objeto sem que
ele fique impedido de ser coletado pelo Garbage Colelctor.

[Agora no PHP 8.0 a classe WeakMap foi adicionada](https://wiki.php.net/rfc/weak_maps).
Weak Maps usam o mesmo conceito de Weak References mas
implementam as interfaces _ArrayAccess_, _Countable_ e
_Traversable_. Isto nos permite criar coleções (maps) que não
impedem que seus objetos sejam destruidos quando todas as
outras referências forem removidas.

Eu pretendo escrever melhor sobre Garbage Collection no PHP
no futuro, mas se você quiser botar uma pressão pra ver este
conteúdo logo me dá um alô lá no Twitter ou abre uma issue pra
que eu dê prioridade a este assunto.

Aqui vai um exemplo de como utilizar WeakMaps:

```php
<?php

$bag = new WeakMap();
$obj = new stdClass();

$bag[$obj] = 42;

// int(1)
var_dump($bag->count());

// deleta $obj da memória
// $bag está vazia agora
unset($obj);

// int(0)
var_dump($bag->count());
```

### Erros e Alertas

Esta RFC mudou a forma como o PHP se comporta e é
importante que a gente preste bastante atenção nela!

Muitas mensagens de erro e nível de criticidade mudaram
para que fiquem mais consistentes. Nenhum nível de
criticidade caiu, apenas cresceram. Alguns Notices se
tornarão Warnings, e alguns Warnings se tornarão
Errors (lançarão exceção).

A lista completa você consegue encontrar
[na página da RFC](https://wiki.php.net/rfc/engine_warnings)
e eu recomendo fortemente que você dê uma lida já que
este tipo de problema pode aparecer de forma bem silenciosa
se você não tiver um bom monitoramento configurado.

### Verificações das Assinaturas de Métodos Mágicos

Esta mudança foi introduzida pelo querido Gabriel Caruso,
do PHPSP, que tive o prazer de conhecer neste ano! Ele
adicionou verificações de tipos nas assinaturas dos
métodos mágicos do PHP da forma definida na documentação.

Toda classe implementando métodos mágicos que não forem
escritas de accortdo com a assinatura irá gerar um `FatalError`
como você pode verificar [na página da RFC](https://wiki.php.net/rfc/magic-methods-signature).
Mesmo sendo uma breaking change, apenas 7 dos top 1000
pacotes no Packagist seriam afetados por esta mudança.

### Correções em Strings Numéricas

O PHP consegue converter strings numéricas em inteiros
quando necessário. Este cast pode acontecer manualmente
ou de forma implícita dependendo de qual operação você
executar (por exemplo, expressões e chamadas de função).

```php
<?php

// int(123)
var_dump((int) "123");
```

Ainda mais, o PHP é uma mãe quando se trata de strings
numéricas: perdoa tudo! Strings como `"2 bananas"` ou
`"5 maçãs"` podem ser convertidas para números normalmente.
Mais do que isso, algumas strings podem ser interpretadas
como numéricas quando não deveriam (como em hashes que
começam com um zero, por exemplo).

[A RFC saner numeric strings](https://wiki.php.net/rfc/saner-numeric-strings)
veio corrigir este problema, normalizadno a forma como
nós lidamos com strings numéricas e gerando Type Errors
quando tipos numéricos são requeridos mas uma string
não numérica é passada.

### Mudanças na Comparação de String Numéricas

O PHP tem dois modos de comparação: estrito (`===`, `!==`)
e não estrito (todo o resto). Sempre que fazemos uma
comparação não estrita entre uma string e um número, o PHP
vai tentar converter a string em um número para só então
comparar dois inteiros.
[Eu explico este processo em detalhe neste post aqui](/br/edicao/tipos-em-php/).

Este comportamento criou distorções bem estranhas como
a expressão `0 == 'nawarian'` retornando `bool(TRUE)`.

[A RFC de comparações de strings numéricas](https://wiki.php.net/rfc/string_to_number_comparison)
melhora estas comparações ao inverter a lógica de conversão:
em vez de converter a string em número e então comparar os dois
números, o PHP irá transformar o número em string e então
comparar as duas strings.

Uma nova tabela de comparação foi disponibilizada na RFC
e eu trouxe uma cópia pra cá:

Comparação    | Antes | Depois
--------------|-------|------
 0 == "0"     | true  | true
 0 == "0.0"   | true  | true
 0 == "foo"   | true  | false
 0 == ""      | true  | false
42 == "   42" | true  | true
42 == "42foo" | true  | false

## Agora é aproveitar e aguardar as próximas

É claro que tem muito mais coisas que o PHP 8.0 trouxe
e eu gostaria muito de ter tido o tempo e vontade para
escrever todas aqui. Mas esta pequena lista já deixa claro
que o PHP, morrendo desde 1994, mais uma vez se torna
melhor e mais poderoso.

Eu atualmente não conheço nenhum benchmark sobre o PHP 8.0
rodando aplicações de verdade que possam dizer que esta
versão é mais rápida, a mesma coisa ou mais lenta. Mas eu
confio que as ferramentas que a comunidade nos deu irão
nos permitir continuar criando aplicações incríveis e
rápidas.

A adição do compilador Just In Time é uma boa oportunidade
para olhar com mais carinho para ferramentas que nós
poderíamos explorar bem melhor como, por exemplo, a Extensão
Swoole.

Agora é momento de celebrar esta incrível vitória da
Comunidade PHP e agradecer todas as pessoas que se envolveram
(você também está nesta lista 😉). A versão 8.1 alpha já
iniciou o desenvolvimento e eu mal posso esperar por o
que vem pela frente!

Por favor, não se esqueça de compartilhar com seus amigos
e colegas, e me dê um toque se você encontrou alguma coisa
estranha aqui ou gostaria de adicionar você mesmo alguma
coisa estranha.

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
  "headline": "PHP 8.0 lançado: tá com uma cara ótima!",
  "description": "PHP 8.0 trouxe várias inovações, dentre elas incríveis mudanças sintáticas, atualizações nas APIs e mudanças fundamentais no core e, claro, várias correções de bug. Aqui eu vou te mostrar as principais mudanças à linguagem!",
  "image": [
    "{{ $page->getBaseUrl() }}/assets/images/posts/19-php-8-features-640.webp"
   ],
  "datePublished": "2020-11-26T00:00:00+08:00",
  "dateModified": "2020-11-26T00:00:00+08:00",
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
