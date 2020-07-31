---
slug: tipos-em-php
lang: pt-br
title: Tudo o que você precisa (e não precisa) saber sobre Tipos no PHP
category: walkthrough
createdAt: 2020-07-29
sitemap:
  lastModified: 2020-07-29
image:
  url: /assets/images/posts/17-php-type-system-640.webp
  alt: 'Uma mulher segurando um livro sobre textos cruéis.'
tags:
  - core
  - curiosidade
  - php8
meta:
  description:
    Este é o melhor guias entre todos que você encontrará na
    internet sobre como o PHP lida com seus tipos internamente.
  twitter:
    card: summary
    site: '@nawarian'
---

[Read in English](/en/issue/php-type-system)

**PHP é uma linguagem dinamicamente tipada** e até o ano de 2015 quase
não tinha suporte para declarar tipos de forma estática. Já era possível
realizar um cast para tipos escalares de forma explícita no código,
mas declarar tipos escalares em assinaturas de métodos e funções
não era possível até a chegada do PHP 7.0 com as RFCs
[Scalar Type Declarations](https://wiki.php.net/rfc/scalar_type_hints_v5)
e [Return Type Declarations](https://wiki.php.net/rfc/return_types).

Mas isso não significa que a partir da versão 7.0 o PHP passou a ser
estaticamente tipado. **O PHP possui type hints que podem ser analizados
de forma estática** mas **ainda oferece suporte a tipos dinâmicos** e,
inclusive, nos permite misturar os dois formatos.

Veja o exemplo abaixo:

```php
<?php

function retornaInt(): int
{
  return '100';
}
```

Sem sombra de dúvidas **tem um conflito de tipos aí em cima**. O retorno
deveria ser um _int_ e o valor retornado é na verdade uma _string_. O que
o PHP faz internamente é automaticamente transformar o token '100' num
inteiro para poder retornar o tipo necessário. Mesmo que pareça trazer um
custo extra, não é o caso. O type juggling (malabarismo de tipos) do php
é _quase_ livre de processamento extra em muitos casos.

Para esclarecer de uma vez por todas como a linguagem lida com tipos, eu
escrevi este arquivo em secções distintas para você:

* [Tipos de tipos no PHP](#tipos-de-tipos-no-php)
* ["Operações" com tipos no PHP](#operacoes-com-tipos-no-php)
* [Os Union Types](#os-union-types)
* [Malabarismo de tipos, ou type juggling](#malabarismo-de-tipos)
* [Os modos de tipagem](#modos-de-tipagem)

Se você tiver alguma sugestão de o que adicionar aqui, sinta-se livre pra
[me dar um toque no twitter](https://twitter.com/nawarian) ou abrir uma
issue no github.

**Aahh!! Se você curte este tipo de conteúdo mais aprofundado e tal, dá
uma ligadinha nesse artigo que eu escrevi sobre
[como funciona o Just In Time compiler](/br/edicao/php-8-jit)
que vai entrar no PHP 8.0! Abre numa abinha aí e lê em seguida, tu não vai
se arrepender! 😉**

<hr />

<h2 id="tipos-de-tipos-no-php">Tipos de tipos no PHP</h2>

O sistema de tipos do PHP é bem simplificado quando se trata de
funcionalidades da linguagem. Por exemplo, não existe um tipo _char_,
ou valores _unsigned_ (sem sinal) ou mesmo as variações de inteiro
_int8_, _int16_, _int32_, _int64_...

O tipo _char_ é simplificado para tornar-se _string_ e todos inteiros
são simplificados em um tipo _integer_. Se isso for ou não uma coisa boa
fica a seu critério.

Você sempre pode inspecionar o tipo de uma variável usando a função
[gettype()](https://www.php.net/manual/en/function.gettype) ou
a função [var_dump()](https://www.php.net/manual/en/function.var-dump).

O PHP vem com três tipos de tipos: **tipos escalares**, **tipos compostos**
e **tipos especiais**.

### Tipos escalares

Tipos escalares são fundamentais na linguagem e são no total quatro:

* Boolean (`bool` | `boolean`)
* Integer (`int` | `integer`)
* Float (`float` | `double`)
* String (`string`)

Por definição, um tipo escalar não possui comportamento ou estado.
Expressões como `100->toString()` ou `'thephp.website'::length()'`
são ilegais!

**Resumão do ENEM: tipos escalares não possuem comportamento ou estado,
eles só representam um valor.**

### Tipos compostos

Tipos compostos são muito mais interessantes porque mesmo que eles sejam
similares aos tipos escalares, **cada um dos quatro tipos compostos possui
diferentes sintaxes**.

Os quatro tipos compostos são:

* [array](#tipo-composto-array)
* [object](#tipo-composto-object)
* [callable](#tipo-composto-callable)
* [iterable](#tipo-composto-iterable)

<h4 id="tipo-composto-array">O tipo composto Array</h4>

Um array na realidade é um hashmap, que vem por padrão com a linguagem PHP.
Isto significa que seus valores são guardados no formato **chave => valor**
mesmo que você o utilize como um vetor.

Arrays são estruturas muito flexíveis quando se trata de tamanho, tipos internos
e mapeamento chave-valor. Os exemplos abaixo são todos arrays válidos:

```php
<?php

$vec = [0, 1, 2];
// $vec[1] é int(1)

$map = ['a' => 1, 'b' => 2];
// $map['a'] é int(1)

$quase_map = ['a' => 1, 0 => 2];
// $quase_map['a'] é int(1)
// $quase_map[0] => é int(2)
```

Diferente do C, o PHP não vai te obrigar a definir o tamanho dos arrays
antes de criá-los. Isto, como era de se esperar, traz um custo em memória:
o quão maior for o tamanho do seu array, mais memória você consumirá em
proporções absurdas (na real, arrays são alocados em potências de 2).
Como este consumo de memória acontece está fora do escopo deste artigo,
[me dá um toque se tu quiser saber mais sobre este tópico em particular](https://twitter.com/nawarian).

Caso você esteja curioso sobre o que eu disse acima, tem uma apresentação
muito interessante do Nikita Popov sobre o consumo de memória entre arrays
e objetos:

<iframe style="margin: auto; margin-bottom: 20px;" width="560" height="315" src="https://www.youtube.com/embed/JBWgvUrb-q8?start=1000" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

Como você poderá verificar abaixo, arrays também são considerados como sendo
do tipo _iterable_, isto significa que você pode iterar sobre eles usando um
laço _foreach_. Mas eles também oferecem
[funções específicas que podem manipular seus ponteiros internos](https://www.php.net/manual/en/ref.array.php)

**Resumão do ENEM: O tipo array é um tipo composto extremamente flexível e pode
ser considerado um HashMap e também é um tipo _iterable_**

<h4 id="compound-type-object">O tipo composto Object</h4>

Por conta da arquitetura do PHP, o tipo composto _object_ normalmente tem um perfil
de consumo de memória bem menor quando comparado aos arrays. Isto porque _normalmente_
uma pessoa usaria o tipo object criando instâncias de classes.

Objetos podem carregar estado e comportamento consigo. Significa que o php oferece
sintaxes para desreferenciar as entranhas de um objeto. O snippet abaixo ilustra
como a operação de desreferência funciona:

```php
<?php

class MinhaClasse
{
  private const A = 1;
  public int $propriedade = 0;
  public function metodo(): void {}
}

$obj = new MinhaClasse();
// $obj é object(MyClass)
// $obj::A é int(1)
// $obj->propriedade é int(0)
// $obj->metodo() é null
```

Um objeto também pode ser criado normalmente como resultado de um type cast a
partir de um array. Transformando as chaves do array em nomes de propriedades.
Este tipo de cast vai resultar em um tipo `object(stdClass)`.

```php
<?php

$obj = (object) ['a' => 1];
// $obj é object(stdClass)
// $obj->a é int(1)
```

Vale ressaltar que converter um array com chaves numéricos em um objeto é válido,
mas não é possível desreferenciar o seu valor porque propriedades de objetos
não podem começar com números.

```php
<?php

$obj = (object) [0, 1]; // Legal
$obj->0; // Ilegal
```

**Resumão do ENEM: objetos normalmente têm um perfil de memória menor que o dos arrays,
carregam consigo estado e comportamento, e podem ser criados ao converter um array.**

<h4 id="tipo-composto-callable">O tipo composto callable</h4>

Um callable (chamável) no php é qualquer coisa que pode ser chamada (é mermo é?) usando
parêntesis ou com a função [call_user_func()](https://www.php.net/manual/en/function.call-user-func.php).
Ou sejE, um callable é capaz de cumprir o papel de o que conhecemos como funções. Funções
e métodos sempre são callables. Objetos e classes também podem se tornar callables.

Um callable pode, por definição, ser guardado numa variável. Como a seguir:

```php
<?php

$callable = 'strlen';
```

Quê?! Mas isso não é uma string, doido!?

Até que é. Mas ele pode ser coagido (coerced) num callable se for necessário. Como abaixo:

```php
<?php

function chameUmCallable(
  callable $f
): int {
  return $f('thephp.website');
}

$callable = 'strlen';

var_dump(
  $callable('thephp.website')
);
// int(14)

var_dump(
  chameUmCallable($callable)
);
// int(14)
```

Callables também podem apontar para um método de um objeto:

```php
<?php

class MinhaClasse
{
  public function meuMetodo(): int
  {
    return 1;
  }
}

$obj = new MinhaClasse();
var_dump([$obj, 'meuMetodo']());
// int(1)
```

Parece estranho? Eu sei que tem cara de array. Na real é um array mesmo. A não ser que
você o trate como um callable 👀

Este tipo de callable acima (referência de método de objeto) é muito interessante porque
**você pode chamar métodos privados ou protegidos** com ele **se você estiver dentro do
escopo da classe.** Caso contrário, você pode somente chamar métodos públicos.

E também as classes que implementam
[o método mágico __invoke()](https://www.php.net/manual/en/language.oop5.magic.php#object.invoke),
automaticamente tranformas suas instâncias em callables. Como a seguir:

```php
<?php

class MinhaClasseCallable
{
  public function __invoke(): int
  {
    return 1;
  }
}

$obj = new MinhaClasseCallable();
var_dump($obj());
// int(1)
```

**Resumão do ENEM: callables são referências para funções ou métodos e podem ser construídos
de maneiras distintas.**

<h4 id="tipo-composto-iterable">O tipo composto iterable</h4>

Iterables são muito mais simples de explicar: eles são, por definição, um array ou uma instância
de [Traversable interface](https://www.php.net/manual/en/class.traversable.php). A coisa mais
importante de um iterable é que ele pode ser usado num
[laço foreach()](https://www.php.net/manual/en/control-structures.foreach.php), num
[yield from](https://www.php.net/manual/en/language.generators.syntax.php#control-structures.yield.from)
ou com o [operador de propagação (spread operator)](https://wiki.php.net/rfc/spread_operator_for_array).

Exemplos de iterables são:

```php
<?php

function funcao_generator(): Generator
{
  // ...
};

// Todas variáveis aqui são iterables
$a = [0, 1, 2];
$b = funcao_generator();
$c = new ArrayObject();
```

**Resumão do ENEM: se você pode colocar num foreach(), é um iterable.**

### Tipos Especiais

Existem dois tipos especiais. E a maior razão pela qual eles são chamados "especiais"
é que **não é possível converter para estes tipos**. Os tipos especiais são o tipo
**resource** e o tipo **NULL**.

**Um resource representa um conector para um recurso externo**. Que pode ser um conector
para um arquivo, um fluxo de E/S ou uma conexão com banco de dados. Você talvez possa
adivinhar o motivo de não poder fazer um cast para qualquer outro tipo de resource.

**O tipo null representa um valor nulo**. Isto significa que uma variável com NULL não
foi inicializada, foi atribuída com o valor NULL ou apagada em tempo de execução.

**Resumão do ENEM: uma variável de tipo especial não pode ser convertida para qualquer outro tipo.**

### E as instâncias de classe?

Instâncias possuem o tipo `object` e serão sempre representadas desta forma. Chamar a função
[gettype()](https://www.php.net/manual/en/function.gettype) num objeto sempre irá retornar
o valor `string("object")` e chamar a função [var_dump()](https://www.php.net/manual/en/function.var-dump)
no mesmo objeto sempre irá imprimir seu valor usando a notação `object(NomeDaClass)`. Se você
precisar pegar a classe de um objeto no formato string, utilize a função
[get_class()](https://www.php.net/manual/en/function.get-class).

```php
<?php

$obj = new stdClass();

echo gettype($obj);
// object

var_dump($obj);
// object(stdClass)#1 (0) {
// ...

echo get_class($obj);
// \stdClass
```

<h2 id="operacoes-com-tipos-no-php">"Operações" com tipos no PHP</h2>

Existem diferentes "operações" que podem ser feitas com tipos no PHP. Eu acho
que é importante deixar bem claro estas operações aqui para que não misturemos
as bolas depois.

### Malabarismo de tipos (type juggling): cast e coerção de tipos

Antes de a gente se aprofundar, aqui vão três definições importantíssimas:

1. **Conversão de tipo** significa transformar um tipo de A para B. Por exemplo: de um inteiro para um float.
1. **Cast de tipos** significa converter **manual** ou **explicitamente** um tipo de A para B. Como em `$cem = (int) 100.0`. (`float(100.0)` virou `int(100)`) 
1. **Coerção de tipo** significa converter **implicitamente** um tipo de A para B. Como em `$vinte = 10 + '10 bananas';`. (`string("10 bananas")` virou `int(10)`)

Tendo isto em mente, as próximos secções vão explicar como isso funciona no php. E mais
pra frente você encontrára mais informações sobre o malabarismo de tipos (type juggling).

#### Cast de tipos

De forma semelhante ao Java, o PHP nos permite fazer cast de tipos. Isto significa que quando
uma variável aponta para um valor que pode ser transformado num tipo diferente, a linguagem
nos permite uma conversão manual (explícita) de tipos.

Pera, pera... É OQUE!? 🤨

Ó: uma variável `$cem` segurando `string("100")` pode ser convertida manualmente (cast) para
tornar-se `int(100)` ou `float(100.0)` - ou qualquer outro tipo escalar ou um dos tipos
compostos _array_ ou _object_.

O snippet a seguir funciona perfeitamente no PHP e é bem parecido com o Java:

```php
<?php

$cem = (int) '100';
// $cem agora é int(100)
```

Agora, uma coisa que o Java faz e é completamente ilegal no php, é converter (cast) um ponteiro
de variável numa classe diferente. **Isto significa que a gente só pode converter tipos escalares
e alguns tipos compostos no php**:

```php
<?php

class MinhaClasse {}

// Gera um parse error
$ilegal = (MinhaClasse) new stdClass();
```

Importante notar! No PHP só é possível fazer cast de tipos para tipos escalares*. Portanto
fazer o cast de um objeto para uma classe diferente é ilegal, mas **fazer um cast de objeto
para um tipo escalar é completamente válido**.

**Também possível fazer o cast de valores para os tipos _array_ ou _object_**, que não são
tipos escalares mas sim compostos (dar nome pr'esses coiso tudo é osso, né?).

```php
<?php

class MinhaClasse {}

$obj = new MinhaClasse();
$um = (int) $obj; // int(1)
```

O código acima gera alguns notices mas ainda assim é válido. Mais tarde eu explico de onde
veio esse `int(1)`.

**Resumão do ENEM: o php permite realizar cast de tipos para escalares, arrays ou objetos.
Fazer o cast para classes não é permitido.**

#### Coerção de tipos

**A coerção de um tipo acontece como um efeito colateral de trabalhar com tipos incompatíveis
ou não declarados.** Eu explico melhor mais pra frente enste artigo. Por agora apenas confia que
o PHP vai automaticamente fazer o cast dos tipos o seu código em tempo de execução quando necessário.

Um exemplo de coerção de tipos pode ser multiplicar um integer por um float. Na expressão `int(100)`
multiplicado por `float(2.0)` o resultado é um `float(200)`.

```php
<?php

var_dump(100 * 2.0);
// float(200)
```

**Resumão do ENEM: o php tem um mecanismo para normalizar tipos em tempo de execução de forma
implícita e você deve sempre prestar atenção nisso!**

### Type hints

O type hinting é um mecanismo de, ao mesmo tempo, reforçar a coerção de tipos e de import tipagem
estrita. Isto foi introduzido ao php na versão 7.0 e transforma assinaturas de métodos e funções.
[Desde o php 7.4 também é possível fazer type hint com propriedades de classes](https://wiki.php.net/rfc/typed_properties_v2).

Abaixo vai um exemplo de type hint:

```php
<?php

function somar(
  int $a,
  int $b
): int {
  return $a + $b;
}
```

As dicas (hints) aqui dizem que a variável `$a` é do tipo _int_ naturalmente ou transformada
pela linguagem, a variável `$b` também é do tipo _int_ e o resultado desta função será do tipo
_int_, de forma natural ou transformada automaticamente pela linguagem (coerção).

Reparou que eu disse que elas são de certo tipo "de forma natural ou transformada automaticamente
pela linguagem (coerção)"? Isso porque o PHP não vai reclamar se você chamar esta função
com valores que não são do tipo int. O que vai acontecer, aliás, é que o php vai tentar converter
implicitamente (coerção) os parâmetros em inteiros se o tipo não for o esperado.

**No corpo da função a seguir você pode sempre ter certeza de que `$a` e `$b` são inteiros. Mas
de que os inteiros estão corretos somente quem chama função pode garantir.**

```php
<?php

function somar(
  int $a,
  int $b
): int {
  // $a é int(10)
  // $b é int(10)
  return $a + $b;
}

somar('10 maçãs', '10 bananas');
```

Também é possível ativar uma diretiva chamada `strict_types` para evitar coerções e
simplesmente gerar erros quando tipos invalidos são utilizados. Como à seguir:

```php
<?php

declare(strict_types=1);

function somar(
  int $a,
  int $b
): int {
  return $a + $b;
}

somar('10 bananas', '10 maçãs');
// PHP Fatal error: Uncaught
// TypeError: Argument 1 passed
// to somar() must be of the type
// int, string given
```

**Isso não significa que o php é estaticamente tipado quando strict_types está
ligado!** Na realidade, o type hinting apenas adiciona um processamento extra.
Internamente ele sempre fará o malabarismo de tipos (type juggling) e nunca
irá confiar nos type hints da sua variável.

Type hints servem a dois propósitos: definir em quais tipos um valor deveria ser
coagido OU gerar erros fatais quando os strict types estiver ligado.

**Resumão do ENEM: type hints apenas dão dicas sobre os tipos para o php, não ordens!
Usar strict types é uma escolha que você pode tomar e trará um pequeno processamento
extra consigo.**

<h2 id="union-types">Union Types</h2>

Antes de a gente falar de malabarismo de tipos (type juggling) eu gostaria de falar
rapidinho sobre os Union Types porque parece fazer mais sentido aqui.

Além dos três tipos que o php tem (escalares, compostos e especiais) o manual do php
também menciona um
[pseudo-tipo que só existe para facilitar a leitura do manual](https://www.php.net/manual/en/language.pseudo-types.php).
Este tipo não existe de verdade, é apenas uma convenção.

Eu gostaria que você prestasse atenção num pseudo-tipo muito específico: o `array|object`
normalmente é utilizado na documentaçnao para especificar parâmetros ou tipos de retorno.

O tipo `iterable` também é um tipo de Union Type. E pode ser definido como `array|Traversable`.

Desde o php 7.1 a linguagem traz um meio-que suporte a Union Types ao ter introduzido
o [nullable type](https://wiki.php.net/rfc/nullable_types). Se você parar pra pensar,
um tipo nullable é apenas um Union de `T|null`. Por exemplo, `?int` significa `int|null`.

Aposto que tu não pensou sobre isso antes! 😝

Então depois de tantos Union Types desconhecidos,
[o php 8.0 formalmente implementou os Union Types](https://wiki.php.net/rfc/union_types_v2).
Onde você pode definir qualquer Union Type que precisar sem depender de pseudo-types ou
convenções. Funciona mais ou menos assim: 

```php
<?php

declare(strict_types=1);

function dividir(
  int $a,
  int $b
): int|float {
  return $a / $b;
}
```

A função acima pode retornar integer ou float. Mas nunca outro tipo.

<h2 id="malabarismo-de-tipos">Malabarismo de tipos, ou type juggling</h2>

Provavelmente não é a primeira vez que você ouviu falar no termo Type Juggling,
certo? Esta é uma das funcionalidades mais importantes do php e, ainda assim, é
uma das menos compreendidas.

Eu não posso culpar ninguém por não entendê-la bem. A gente chama isso de "malabarismo"
por um bom motivo. Uma variável pode assumir tanto tipo diferente em cada contexto
que pode ser um tanto complicado entender com qual tipo você está lidando.

Vamos começar com o seguinte: **o php não permite definir tipos explicitamente
na declaração de variáveis**. E isso é muito poderoso!

Sempre que você declara uma variável, o php vai inferir o tipo que ela possui
baseado no valor que você a deu. Enquanto `$var;` cria uma variável com valor NULL,
`$one = 1` cria um inteiro e `$obj = new stdClass()` cria um `object(stdClass)`.

Aí não tem definição de tipo em canto algum! O php vai tomar conta de adivinhar
qual o tipo melhor se adequa a sua variável.

As variáveis do php são muito dinâmicas, de forma que elas podem mudar de tipo
em tempo de execução sem problema algum! O código abaixo é válido:

```php
<?php

$var;
// $var é NULL

$var = 1;
// $var é int(1)

$var = 'thephp.website';
// $var é string("thephp.website")

$var = new stdClass();
// $var é object(stdClass)
```

E por as variáveis serem tão dinâmicas, várias operações no php exigem que os
valores sejam verificados baseado no contexto da operação. Uma expressão como a
soma (a + b) internamente irá verificar o tipo do primeiro operando e depois tentar
adivinhar o tipo do segundo operando. 

Dê uma sacada [nesse snippet do código fonte do php](https://github.com/php/php-src/blob/PHP-7.4/Zend/zend_vm_def.h#L47-L84).
Se `op1` for long (a é um inteiro) então verifique se `op2` também é long (b é inteiro).
Se sim, faça uma soma de longs. Se não, verifique se `op2` é um double e faça uma
soma de doubles se sim. E esta expressão
[pode retornar um inteiro](https://github.com/php/php-src/blob/PHP-7.4/Zend/zend_vm_def.h#L61)
ou [um float](https://github.com/php/php-src/blob/PHP-7.4/Zend/zend_vm_def.h#L74).

**E é por isso que eu te garanto que o malabarismo de tipos (type juggling) vai acontecer
automaticamente.**

Isso também significa que coerção de tipos (conversões implicitas) vão acontecer automaticamente.
Mas elas não deveriam ser uma surpresa! Há momentos muito específicos onde uma coerção
de tipos deve acontecer.

Coerção de tipos (e, portanto, malabarismo de dados) ocorrem quando:

* resolvem-se expressões
* passam-se argumentos para uma função ou método
* retorna-se de uma função ou método

Você pode estar se perguntando: ué, se coerção acontece em todo canto então como
o php lida com tipos incompatíveis? Converter um inteiro para boolean parece normal,
mas um array para inteiro já começa a ficar estranho.

Bem, o php tem regras muito bem definidas para fazer conversão de tipos. Primeiro
entende-se qual o tipo que o resultado deveria ter e só então é feita a conversão.

Por exemplo, se uma expressão ocorrer dentro de um `if()` a gente pode perceber
rapidinho que aquela expressão deve resultar em um tipo boolean.

```php
<?php

$var = 100;
// $var é int(100)

// $var é tratado como
// boolean e resulta
// em TRUE
if ($var) {
  // $var ainda é int(100)
}

// $var ainda é int(100)
```

Repare como $var era `int(100)` durante todo seu ciclo de vida, mas foi tratada como
`bool(TRUE)` dentro daquele _if()_. Isto ocorre porque o _if()_ espera uma expressão
que retorna um boolean. O malabarismo de tipos (type juggling) é justamente o que o
php fez por debaixo dos panos para você. 

Para ilustrar, aqui vai a lista de verificações ao converter um tipo em boolean.
**Uma conversão para boolean retornará false quando o valor original for**:

* um bool(FALSE)
* um `int(0)` ou `int(-0)`
* um `float(0)` ou `float(-0)`
* uma string vazia `string("")` ou a string zero `string("0")`
* um array vazio `array()`
* um NULL
* uma instância de SimpleXML criada a partir de tags vazias

**E irá retornar true para qualquer outro valor.**

A tabela acima pode ser encontrada na
[seção "Converting to boolean" do manual](https://www.php.net/manual/en/language.types.boolean.php#language.types.boolean.casting).

A documentação completa sobre as comparações de tipos e tabelas de conversões
[também podem ser encontradas no manual da linguagem](https://www.php.net/manual/en/types.comparisons.php).
Eu não tomei coragem de ler, mas faz parte do meu trabalho dizer que elas existem e
te mostrar onde 🤷🏻‍♀️

**Nota importante aqui**: no php 8.0 os union types foram introduzidos e trouxeram consigo
uma camada extra de complexidade. O malabarismo de dados (type juggling) quando lida com
Union Types precisa seguir uma regra de precedência. E esta precedência é pré-definida em
vez de depender da ordem dos tipos declarados.

[Então se você não estiver usando strict_types os seus Union Types vão seguir esta regra](https://wiki.php.net/rfc/union_types_v2#coercive_typing_mode).
Se o Union Type não contém o tipo do resultado, ele poderá fazer a coerção deste valor na
seguinte ordem de precedência: `int`, `float`, `string` e `bool`.

Por exemplo:

```php
<?php

function f(
  int|string $v
): void {
  var_dump($v);
}

f(""); // string ESTÁ no union type
// string("")

f(0); // int ESTÁ no union type

f(0.0); // float NÃO ESTÁ no union type
// int(0)

f([]); // array NÃO ESTÁ no union type
// Uncaught TypeError:
// f(): Argument #1 ($v)
// must be of type string|int
```

No exemplo acima algo interessantíssimo acontece! O tipo array não será convertido
para um `bool(FALSE)`. Ele gera um TypeError em vez disso!

<h2 id="modos-de-tipagem">Os modos de tipagem</h2>

**Você já deve ter percebido que existem duas formas de o php lidar com tipos**. Uma
delas é chamada **"Coercive Type Mode"** onde acontece todo aquele malabarismo e
adivinhações de tipos. A outra é o **"Strict Type Mode"** onde **o malabarismo e
a adivinhação ainda acontecem**, mas **quando os tipos são definidos explicitamente**
alguns **TypeErrors serão lançados quando os tipos não forem compatíveis**.

Agora, eu vejo como algo normal que pessoas programadoras de php possam esperar que a
linguagem respeite a Lei da Troca Equivalente (等価交換法) e lhe pague com ganho de
performance o esforço de usar strict types porque ela será então capaz de pular todas
as verificações de tipos e executar as operações diretamente.

Ao passo que eu entendo o motivo de alguém pensar desta forma, eu preciso lhe dizer:
está completamente errado! O código a seguir contém
[a lógica da função strlen() no código fonte do php](https://github.com/php/php-src/blob/PHP-7.4/Zend/zend_vm_def.h#L8056-L8105).

Toda vez que é necessário verificar se o php está operando no modo "Strict Type", pode-se
buscar o boolean a partir da chamada `EX_USES_STRICT_TYPES()`. Se true, o strict types
está ligado. Se não, o modo coercivo está.

Agora, veja o snippet novamente! Ele começa assim:

```c
// ...
zval *value;

value = GET_OP1_ZVAL_PTR_UNDEF(BP_VAR_R);
// value é o parâmetro
// de strlen()

if (EXPECTED(
  Z_TYPE_P(value) == IS_STRING
)) {
  ZVAL_LONG(
    EX_VAR(
      opline->result.var
    ),
    Z_STRLEN_P(value)
  );
  FREE_OP1();
  ZEND_VM_NEXT_OPCODE();
} else {
  // ...
}
```

Reparou naquele primeiro _if()_ alí? Adivinha o que ele tá fazendo... EXATO! Ele
verifica pra ti o tipo do parâmetro!!

Sabe o que esse mesmo trecho de código está fazendo com o seu type hint? NADINHA! 🤣

A cláusula _else_ possui o código TALVEZ vá usar strict types ou não.

```c
// ...
} else {
  // Ok, estamos progredindo
  zend_bool strict;

  // 😭
  if (
    (OP1_TYPE & (IS_VAR|IS_CV)) &&
    Z_TYPE_P(value) == IS_REFERENCE
  ) {
      // ...
  }

  // ...

  // OPA! 👀
  strict = EX_USES_STRICT_TYPES();
  do {
    if (EXPECTED(!strict)) {
      // ...
    }
    zend_internal_type_error(
      strict,
      /*...*/
    );
    ZVAL_NULL(
      EX_VAR(opline->result.var)
    );
  } while (0);
}
```

No trecho acima podemos ver um exemplo de como o modo strict type não corta nenhum
processamento. Na verdade, acabou criando algumas verificações a mais com um único
propósito: gerar erros fatais. 

Eu não quero dizer que esta é uma implementação ruim. Eu pessoalmente estou bem
contente com a forma que o php funciona. Mas eu acho que é importante deixar claro
que isto não irá afetar a performance de forma positiva.

**Resumão do ENEM: strict types não tornarão seu código mais rápido!**

## Conclusão

Esse artigo deu trabalho ein! Me fez considerar um bom tanto a ideia de escrever um livro.
Só este artigo já daria uns 15% de um livro bacana 😂

Eu espero que a informação que eu coletei aqui foi útil pra ti. E se não foi, que tenha
sido ao menos interessante.

Eu acredito que o sistema de tipos do PHP é incrivelmente rico e carrega várias funcionalidades
legadas e também inovadoras e todas elas fazem muito sentido quando você olha para a
história do desenvolvimento da linguagem.

Como sempre, sinta-se livre para me dar um alô no twitter se você tiver algo a dizer. Você
também pode abrir uma issue ou pull request no github e ser feliz.

**Resumão do ENEM: deu um trabalhão da penga escrever E TRADUZIR este artigo. Se você quiser
dar aquela força, por favor compartilhe em seus círculos e mídias sociais 🙏**

Até a próxima! Valeu!

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
  "headline": "Tudo o que você precisa (e não precisa) saber sobre Tipos no PHP",
  "description": "Este é o melhor guias entre todos que você encontrará na internet sobre como o PHP lida com seus tipos internamente.",
  "image": [
    "{{ $page->getBaseUrl() }}/assets/images/posts/17-php-type-system-640.webp"
   ],
  "datePublished": "2020-07-25T00:00:00+08:00",
  "dateModified": "2020-07-25T00:00:00+08:00",
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
