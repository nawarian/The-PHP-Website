---
slug: andamento-php-8
lang: pt-br
title: O Andamento do PHP 8
createdAt: 2020-01-20
sitemap:
  lastModified: 2020-01-20
image:
  url: /assets/images/posts/5-php-8-640.webp
  alt: 'Um bloco de notas aberto, com páginas em branco e uma caneta sobre si.'
tags:
  - php8
  - core
  - noticias
meta:
  description:
    O PHP 8.0 ainda está sob discussão e muitas coisas estão sendo
    votadas neste momento. Eu coletei todas as mudanças a serem
    introduzidas no PHP 8.0 e vou mantê-lo(a) atualizado(a) sobre
    elas neste post.
  twitter:
    card: summary
    site: '@nawarian'
---

[Read in English](/en/issue/state-of-php-8/)

O PHP 8.0 está [sob discussão e desenvolvimento neste momento](https://wiki.php.net/rfc#php_80).
Isso significa que muitas coisas neste post ainda vão mudar muito com o tempo. Para cada
área de interesse eu vou deixar um subtítulo e, conforme as conversas vão para frente na
comunidade php, eu vou atualizar cada um de acordo.

Devo deixar claro que não serei capaz de atualizar este texto a cada atualização em tempo real,
pois várias alterações acontecem todos os dias. Se você procura por uma lista atualizada por favor
acompanhe o [arquivo UPGRADE no repositório oficial](https://github.com/php/php-src/blob/master/UPGRADING).

## Quando o PHP 8.0 será lançado?

Como [o PHP 7.4 foi lançado há pouco tempo](https://www.php.net/releases/7_4_0.php) os
esforços para o PHP 8.0 ainda estão em fase inicial.

**Neste momento, dia 20 de Janeiro de 2020: ainda não há data de lançamento prevista.**
A [lista oficial de afazeres para lançamentos do PHP](https://wiki.php.net/todo) ainda não
mencionou a versão 8.0.

**Atualização (21/01/2020):** A lista oficial de RFCs adicionou há pouco uma
[seção para o PHP 8.0](https://wiki.php.net/rfc#php_80). Então, diferente do que alguns(umas)
desenvolvedores(as) PHP temiam, **o PHP 8 será desenvolvido durante este ano**.

Além disso [neste comentário da Sara Golemon](https://externals.io/message/105001#105010)
mostrou-se a intenção de lançar algumas snapshots para teste antes mesmo das versões alpha.

[Alguns dizem que será lançado em Setembro de 2021](https://twitter.com/Crell/status/931427244760846336)
mas me parece mais uma piada de twitter do que algo sério.

---

## Funcionalidades aceitas para a versão 8.0

As funcionalidades listadas abaixo **serão entregues com a versão 8.0**. Elas já
foram votadas, aceitas E implementadas.

Então se você quer ter um gostinho do que vem nesta versão, dá uma ligada nisso:

<!-- https://wiki.php.net/rfc/jit -->
### JIT: Just in Time Compiler
- **Status**: Confirmado.
- **Categoria**: Performance.
- **Votos**: 50 sim. 2 não.

Claro que esta funcionalidade precisa de maior explicação e merece um post para
si, escreverei em breve. Enquanto isso posso citar que esta funcionalidade diz
ganhar performance até **quatro vezes mais rápida** no benchmark Mandelbrot.

Você pode ver a especificação e discussão [visitando a RFC](https://wiki.php.net/rfc/jit). 

<!-- https://wiki.php.net/rfc/union_types_v2 -->
### Union Types V2
- **Status**: Confirmado.
- **Categoria**: Sintaxe.
- **Votos**: 61 sim. 5 não.

A [RFC Union Types V2](https://wiki.php.net/rfc/union_types_v2) permitirá
explicitar todos os possíveis tipos aceitos em vez do bom e velho _mixed_.

A nova sintaxe ficará como a seguinte:

```php
function myFunction(int|float $number): int
{
  return round($number);
}
```

<!-- https://wiki.php.net/rfc/weak_maps -->
### A classe WeakMap
- **Status**: Confirmado.
- **Categoria**: Biblioteca Padrão.
- **Votos**: 25 sim. 0 não.

A [RFC da classe WeakMap](https://wiki.php.net/rfc/weak_maps) cria uma nova classe
chamada `WeakMap` que parece um pouco com a `SplObjectStorage`.

A ideia é que você poderá criar um map `objeto -> valor` com ela, sem impeding
que o objeto seja apagado pelo garbage collector. Por isso o nome `Weak`, justamente
porque existe uma **referência fraca (weak reference) entre o objeto chave e o map**.

Removendo um objeto mapeado da memória causará a remoção do valor dentro do map.
Como no seguinte trecho de código:

```php
$map = new WeakMap();
$obj = new DateTime('today');

$map[$obj] = 100;

// Mostra uma chave
var_dump($map);

// Remove $obj da memória
unset($obj);

// WeakMap está vazio
var_dump($map);
```

**Atualização (20/01/2020)**: se você quiser experimentar, tem um polyfill
disponível para o PHP 7.4; Chama-se [BenMorel/weakmap-polyfill](https://github.com/BenMorel/weakmap-polyfill).

<!-- https://wiki.php.net/rfc/consistent_type_errors -->
### Exceções do tipo TypeError serão lançadas em erros de parâmetros
- **Status**: Confirmado.
- **Categoria**: Biblioteca Padrão.
- **Votos**: 50 sim. 2 não.

Sempre que você causa um type error numa função de usuário, uma exceção
é lançada. Para funções internas o PHP apenas mostra um warning e retorna
`null` por padrão.

[A RFC de TypeError consistentes](https://wiki.php.net/rfc/consistent_type_errors)
torna os dois comportamentos consistentes, lançando uma exceção de TypeError
nos dois casos.

<!-- https://wiki.php.net/rfc/negative_array_index -->
### Chaves de array implícitas serão mais consistentes
- **Status**: Confirmado.
- **Categoria**: Biblioteca Padrão.
- **Votos**: 17 sim. 2 não.

Sempre que você utiliza índices negativos na função `array_fill`, ela irá
gerar o primeiro índice negativo e depois pular para 0 (🤦‍♀️). Tipo assim:

```php
$a = array_fill(-2, 3, true);
var_dump($a);

// Saída
array(3) {
  [-2] =>
  bool(true)
  [0] =>
  bool(true)
  [1] =>
  bool(true)
}
```

A [RFC Negative Array Index](https://wiki.php.net/rfc/negative_array_index)
visa corrigir esse comportamento fazendo com que o `array_fill` ande corretamente
pelos índices negativos:

```php
$a = array_fill(-2, 3, true);
var_dump($a);

// Saída
array(3) {
  [-2] =>
  bool(true)
  [-1] =>
  bool(true)
  [0] =>
    bool(true)
  }
```

<!-- https://wiki.php.net/rfc/lsp_errors -->
### Fatal Error em sobrecarga de métodos
- **Status**: Confirmado.
- **Categoria**: Biblioteca Padrão.
- **Votos**: 39 sim. 3 não.

Sempre que uma classe define uma assinatura de método e uma herança tenta
sobrecarregar este método (mudando sua assinatura) um warning é lançado.

[Esta RFC do Nikita Popov](https://wiki.php.net/rfc/lsp_errors) transforma
este comportamento para lançar um Fatal Error sempre que uma sobrecarga ocorre.

Aqui vai um exemplo de código que quebrará no PHP 8:

```php
class A
{
  function x(int $a): int
  {
    // ...
  }
}

class B extends A
{
  // A assinatura mudou
  // Fatal Error aqui.
  function x(float $a): float
  {
    // ...
  }
}
```

<!-- https://wiki.php.net/rfc/dom_living_standard_api -->
### Atualização da DOM API para bater com o padrão DOM
- **Status**: Confirmado.
- **Categoria**: Biblioteca Padrão.
- **Votos**: 37 sim. 0 não.

[Esta RFC](https://wiki.php.net/rfc/dom_living_standard_api) também pede um post
para si.

Mas basicamente ela adiciona algumas interfaces e classes para tornar a API
da `ext/dom` compatível com o [atual padrão DOM](https://dom.spec.whatwg.org/)
que está constantemente sendo atualizado.

---

## O que TALVEZ entre na versão 8.0 do PHP?

Existem algumas RFCs que ainda estão sendo discutiads. Elas podem ser aceitas
ou negadas a qualquer momento. Existem muitas coisas relacionadas ao core da
linguagem e sua sintaxe.

Aqui vai a lista:

<!-- https://wiki.php.net/rfc/engine_warnings -->
### Severidade de erros
- **Status**: Aceita. Implementação pendente.
- **Categoria**: Biblioteca Padrão.

A [RFC da severidade de erros](https://wiki.php.net/rfc/engine_warnings)
pretende revisar a forma como algumas funcionalidades tratam erros na
linguagem.

Por exemplo, o tão famoso `Invalid argument supplied for foreach()`
poderá pular de `Warning` para `TypeError Exception`.

<!-- https://wiki.php.net/rfc/class_name_literal_on_object -->
### Permitir o uso de ::class em objetos
- **Status**: Implementada. Sob Discussão.
- **Categoria**: Sintaxe.

Basicamente nomes de classe dinâmicos não são permitidos em tempo de compilação.
Então um código como o seguinte gera um erro fatal:

```php
$a = new DateTime();
var_dump($a::class);
// PHP Fatal error:  Dynamic
// class names are not allowed
// in compile-time
// ::class fetch in...
```

[Nesta RFC](https://wiki.php.net/rfc/engine_warnings) o código acima será aceitável.

<!-- https://wiki.php.net/rfc/static_return_type -->
### Tornar _static_ um tipo de retorno válido, como _self_
- **Status**: Implementada. Sob Discussão.
- **Categoria**: Sintaxe.

Da mesma forma como podemos usar `self` como tipo de retorno para funções,
[a RFC de static return](https://wiki.php.net/rfc/static_return_type) torna disponível
`static` como outro tipo válido de retorno.

Desta forma **funções como a seguinte serão consideradas válidas:**

```php
class A
{
  public function b(): static
  {
    return new static();
  }
}
```

<!-- https://wiki.php.net/rfc/variable_syntax_tweaks -->
### Sintaxe de variáveis consistentes
- **Status**: Implementada. Sob Discussão.
- **Categoria**: Sintaxe.

Esta aqui é sobre mudanças sintáticas e mudará algumas funcionalidades.

Eu recomendo dar uma olhada [na RFC](https://wiki.php.net/rfc/variable_syntax_tweaks)
para obter mais detalhes. As funcionalidades afetadas incluem:

- Strings interpoladas e não interpoladas
- Constantes and constantes mágicas
- "Dereferenciabilidade" de constantes
- "Dereferenciabilidade" de constantes de classes
- Suporte arbitrário a expressões para `new` e `instanceof`

<!-- https://wiki.php.net/rfc/use_global_elements -->
### Otimizar o lookup de funções e constantes
- **Status**: Prova de Conceito Implementada. Sob Discussão.
- **Categoria**: Sintaxe. Performance.

A [RFC sobre lookup de funções e constantes](https://wiki.php.net/rfc/use_global_elements)
adiciona um novo `declare()` que impede o PHP de fazer alguns lookups em tempo
de execução.

Sempre que você estiver num código dentro de namespace e tenta utilizar
uma função ou constante de escopo global sem prefixar com uma barra invertida (`\`),
o PHP primeiro tentará buscá-la no namespace atual e só então procurar no namespace
global.

Adicionando a diretiva `disable_ambiguous_element_lookup=1`, o PHP tentará buscar
diretamente no escopo global. Aqui vai um exemplo (da RFC):

```php
namespace MyNS;
declare(
    strict_types=1,
    disable_ambiguous_element_lookup=1
);
use function OtherNS\my_function;
use const OtherNS\OTHER_CONST;
 
if (
  // lookup de função!!
  version_compare(
    // lookup de constante!!
    PHP_VERSION,
    '8.0.5'
  ) >= 0
) {
    // ...
}
```

Caso `disable_ambiguous_element_lookup` fosse `zero` no exemplo acima,
o PHP tentaria encontrar `MyNS\PHP_VERSION` e `MyNS\version_compare` primeiro,
entenderia que não existem e só então buscaria no escopo global as referências
para `\PHP_VERSION` e `\version_compare`.

Quando `disable_ambiguous_element_lookup` for `um`, este lookup extra
não é mais necessário e o PHP irá diretamente ao escopo global, trazendo
`\PHP_VERSION` e `\version_compare`.

<!-- https://wiki.php.net/rfc/strict_operators -->
### A diretiva Strict Operators
- **Status**: Prova de Conceito Implementada. Sob Discussão.
- **Categoria**: Sintaxe.

[A RFC de operadores estritos](https://wiki.php.net/rfc/strict_operators) traria
uma nova diretiva chamada `strict_operators`. Quando ligada, algumas comparações
se comportariam de forma diferente.

Aqui alguns exemplos (da RFC):

```php
10 > 42;        // false
3.14 < 42;      // true
 
"foo" > "bar";  // TypeError("Unsupported type string for comparison")
"foo" > 10;     // TypeError("Operator type mismatch string and int for comparison")
 
"foo" == "bar"; // false
"foo" == 10;    // TypeError("Operator type mismatch string and int for comparison")
"foo" == null;  // TypeError("Operator type mismatch string and null for comparison")
 
true > false;   // true
true != 0;      // TypeError("Operator type mismatch bool and int for comparison")
 
[10] > [];      // TypeError("Unsupported type array for comparison")
[10] == [];     // false

"120" > "99.9";               // TypeError("Unsupported type string for comparison")
(float)"120" > (float)"99.9"; // true
 
"100" == "1e1";               // false
(int)"100" == (int)"1e2";     // true
 
"120" <=> "99.9";             // TypeError("Unsupported type string for comparison")
```

As mudanças são bem mais amples que este pequeno exemplo e estão fora do escopo
deste post. Verifique a RFC para mais ou me dá um ping no twitter caso queira que
eu escreva um pouco mais sobre esta! 😉

---

As RFCs abaixo ainda estão sob discussão e a maioria têm algo relacionado a versões
passadas do PHP, não tendo sido lançadas a tempo ou algo parecido. Eu não irei as
descrever em detalhe por agora, por não sentir que trarão grandes mudanças à linguagem.

Eu irei, é claro, manter esta lista atualizada para tomar certeza de que estou errado.

Aqui vão elas:

<!-- https://wiki.php.net/rfc/normalize-array-auto-increment-on-copy-on-write -->
### Auto Increment na cópia na gravação 
- **Status**: Sob Discussão.
- **Categoria**: Sintaxe.

[Link para a RFC.](https://wiki.php.net/rfc/normalize-array-auto-increment-on-copy-on-write)

Esta RFC foi originalmente pensada para o PHP 7.4 e ainda está sob discussão. Eu
esperaria que fosse apontada para a versão 8.0 desta vez, mas não há certezas.

<!-- https://wiki.php.net/rfc/alternative-closure-use-syntax -->
### Sintaxe alternativa do "use" em Closures
- **Status**: Sob Discussão.
- **Categoria**: Sintaxe.

[Link para a RFC.](https://wiki.php.net/rfc/alternative-closure-use-syntax)

Esta RFC originalmente buscava ser integrada na "próxima minor verson", que naquele
tempo seria a versão 7.4.

<!-- https://wiki.php.net/rfc/namespace_scoped_declares -->
### Aplicar um declare() em todo o Namespace 🔥
- **Status**: Implementada. Sob Discussão.
- **Categoria**: Sintaxe.

[Link para a RFC.](https://wiki.php.net/rfc/namespace_scoped_declares)

<!-- https://wiki.php.net/rfc/trailing_whitespace_numerics -->
### Permitir espaços no fim de strings numéricas
- **Status**: Implementada. Sob Discussão.
- **Categoria**: Sintaxe.

[Link para a RFC.](https://wiki.php.net/rfc/trailing_whitespace_numerics)

Esta RFC também visava a versão 7.4 mas não conseguiu ser votada a tempo.

<!-- https://wiki.php.net/rfc/nullable-casting -->
### Permitir type casting de valores nullable
- **Status**: Perdida. Sob Discussão.
- **Categoria**: Sintaxe.

[Link para a RFC.](https://wiki.php.net/rfc/nullable-casting)

Aparentemente o fork que continha as mudanças foi apagado e o Pull Request fechado.
Não parece crível que será integrado ao PHP a menos que alguém resolva tomar conta. 

---

Por enquanto é só. Eu adicionarei algumas **Atualizações** nos tópicos acima com o
tempo, sempre que a comunidade andar com alguma RFC e eu tiver a oportunidade de ver
algum status mudando.

Se você encontrou algo errado ou gostaria de adicionar alguma coisa que eu deixei
passar aqui, sinta-se convidado(a) a me dar um ping no twitter ou abrir uma issue
no [repositório público](https://github.com/nawarian/The-PHP-Website).

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
  "headline": "O Andamento do PHP 8",
  "description": "O PHP 8.0 ainda está sob discussão e muitas coisas estão sendo votadas neste momento. Eu coletei todas as mudanças a serem introduzidas no PHP 8.0 e vou mantê-lo(a) atualizado(a) sobre elas neste post.",
  "image": [
    "{{ $page->getBaseUrl() }}/assets/images/5-php-8.jpg"
   ],
  "datePublished": "2020-01-20T00:00:00+08:00",
  "dateModified": "2020-01-20T00:00:00+08:00",
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
