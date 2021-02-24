---
isFeatured: true
slug: operacoes-bitwise-php
title: Operações binárias (bitwise) com PHP
category: walkthrough
createdAt: 2021-02-24
sitemap:
  lastModified: 2021-02-24
image:
  url: /assets/images/posts/20-bitwise-php/cover-640.webp
  alt: 'Uma silhueta humana pintada com zeros e uns'
tags:
  - curiosidade
  - binário
  - serialização
meta:
  description:
    Recentemente eu trabalhei em diferentes projetos
    que me forçaram a usar bastante operações com
    binários em PHP. De ler arquivos a emular
    processadores, este é um conhecimento
    interessantíssimo e muito útil.
  twitter:
    card: summary
    site: '@nawarian'
---

Recentemente eu trabalhei em diferentes projetos que me forçaram a usar bastante operações com binários em PHP. De ler arquivos a emular processadores, este é um conhecimento interessantíssimo e muito útil.

PHP tem várias ferramentas pra lhe dar suporte a manipulação de dados em formato binário, mas é bom saber desde o começo: se você está buscando eficiência de ultra baixo nível, PHP não é a sua linguagem.

Mas continua aqui! **Neste artigo eu vou te mostrar algumas coisas importantíssimas sobre operações bitwise, como lidar com binários e hexadecimais, e conhecimentos que lhe serão úteis em QUALQUER linguagem.**

## Por que PHP talvez não seja a melhor linguagem pra isso?

Veja bem, eu amo PHP, tá? Não me leve a mal. E eu tenho certeza de que PHP é capaz de lidar com muito mais casos do que você possa imaginar. Mas se você precisa ser extremamente eficiente quando lidar com binários, o PHP não vai segurar a barra.

Clarificando: eu não tô falando de uma aplicação que possa consumir 5 ou 10mb a mais. Eu estou falando sobre alocar o montante exato necessário pra determinado tipo de dado.

De acordo com a [documentação oficial sobre o tipo integer](https://www.php.net/manual/pt_BR/language.types.integer.php), PHP representa números decimais, hexadecimais, octais e binários com o tipo _integer_. Então não importa muito o valor que você coloque numa variável deste tipo, ela será sempre um _integer_.

Você provavelmente já ouviu falar do ZVAL antes, aquela _struct_ em C que representa toda variável PHP. Esta _struct_ tem [um campo para representar todos os integers chamado zend_long](https://github.com/php/php-src/blob/da0663a337b608a4b0008672b494e3a71e6e4cfc/Zend/zend_types.h#L286). Como você pode ver, _zend\_long_ é do tipo _lval_, cujo tamanho depende da plataforma (32 ou 64 bits): numa plataforma 64 bits, [será um integer de 64 bits](https://github.com/php/php-src/blob/74f3bfc6eb7ec80287178e46bd5c269fd371ce5a/Zend/zend_long.h#L30-L31), enquanto numa plataforma 32 bits, [será um integer de 32 bits](https://github.com/php/php-src/blob/74f3bfc6eb7ec80287178e46bd5c269fd371ce5a/Zend/zend_long.h#L40-L41).

```
# zval guarda todo integer como lval
typedef union _zend_value {
  zend_long lval;
  // ...
} zend_value;

# lval é um integer 32 ou 64-bit
#ifdef ZEND_ENABLE_ZVAL_LONG64
 typedef int64_t zend_long;
 // ...
#else
 typedef int32_t zend_long;
 // ...
#endif
```

Em suma: não importa se você precisa guardar os valores _0xff_, _0xffff_, _0xffffff_ ou o que for. Todos serão armazenados como um long (_lval_) com 32 ou 64 bits no PHP.

Eu recentemente trabalhei na emulação de um microcontrolador e, ao mesmo tempo que tratar a memória e operações corretamente é essencial, eu não me importei tanto com a eficiência na alocação de memória porque o meu computador consegue compensar isto em ordens de grandeza.

É claro que tudo muda quando você fala sobre extensões em C ou FFI, mas não é disso que eu tô falando. Eu tô falando de PHP puro! (PHP das ruas como diria o grande PokémãoBR xD)

Então lembre-se: trabalhar dados binários em PHP funciona e você consegue desenvolver qualquer aplicação que quiser, mas os tipos não vão encaixar de forma eficiente na maioria das vezes.

## Uma breve introdução aos formatos binário e hexadecimal

Bom, antes de a gente falar sobre como o PHP trabalha com dados binários, a gente precisa parar um pouquinho e falar sobre binários antes. Se você acha que já sabe tudo o que precisa sobre binários, pode [pular direto para a seção "Números e Strings binárias no PHP"](#numeros-e-strings-binarias-no-php).

Existe um negócio na matemática chamado "base". A base define como nós podemos representar quantidades em diferentes formatos. Nós, humanos, normalmente utilizamos a base decimal (base 10) que nos permite representar números somente com os dígitos 0, 1, 2, 3, 4, 5, 6, 7, 8 e 9.

Pra deixar nossos exemplos mais simples eu vou chamar o número "20" de "20 decimal".

Números binários (base 2) podem representar qualquer número, mas apenas utilizando dois dígitos: 0 e 1.
O 20 decimal pode ser representado em binário como 0b000**10100**. Não se preocupe em converter este número, deixa que o computador faz isso pra ti 😉

Números hexadecimais (base 16) podem representar qualquer número e utilizam não somente os dez dígitos que vimos na base 10 (0, 1, 2, 3, 4, 5, 6, 7, 8 e 9) mas também seis caracteres do alfabeto latino: a, b, c, d, e, e o caractere f.

O 20 decimal pode ser representado como 0x**14** em hexadecimal. De novo, não tente converter na sua cabeça: deixa que os computadores são especialistas nisso!

**O que é importante você entender é que números podem ser representados em diferentes bases:** binária (base 2), octal (base 8), decimal (base 10, a nossa base comum) e hexadecimal (base 16).

Em PHP e diversas linguagens, **números binários** são escritos normalmente mas com um **prefixo 0b**, como o 20 decimal foi representado assim: **0b**00010100. **Números hexadecimais** recebem um **prefixo 0x**, como o 20 decimal que foi representado assim: **0x**14.

Você já deve ter ouvido falar: computadores não guardam dados da forma como nós entendemos. Tudo é representado utilizando números binários: zeros e uns (0 e 1). Caracteres, números, símbolos, instruções... tudo é representado usando base 2. Caracteres são somente uma convenção de números em sequência: o caractere ‘a’, por exemplo, é o número 97 na tabela ASCII.

Mesmo com tudo guardado em formato binário, a forma mais conveniente para programadores(as) lerem estes valores é utilizando hexadecimais. Tipo... a gente lê eles como se fosse poema, se liga:

```
# string "abc"
‘abc’

# formato binário
0b01100001 0b01100010 0b01100011

# formato hexadecimal <3
0x61 0x62 0x63
```

Enquanto os binários tomam um espaço visual enorme, hexadecimais são bem arrumadinhos. É por este motivo que normalmente utilizamos hexadecimais quando lidamos com programação de baixo nível.

## Operações de "vai um" (carry)

Você já conhece o conceito de "vai um", mas eu preciso que você preste atenção nele para que possamos utilizar diferentes bases.

Na base decimal nós conseguimos representar números utilizando apenas dez dígitos, do zero (0) ao nove (9). Mas sempre que você tentar representar qualquer número maior que 9 nós não temos mais dígitos disponíveis! Então o que a gente precisa fazer é adicionar um prefixo com o dígito um (1) e devolver o dígito à direita para zero (0).

```
# decimal (base 10)
1 + 1 = 2
2 + 2 = 4
9 + 1 = 10 // <- vai um
```

Na base binária temos o mesmo comportamento, mas limitados aos dígitos 0 e 1.

```
# binário (base 2)
0 + 0  = 0
0 + 1  = 1
1 + 1  = 10 // <- vai um
1 + 10 = 11
```

E a mesma coisa acontece com a base hexadecimal, mas com uma faixa mais ampla.

```
# hexadecimal (base 16)
1 + 9  = a // sem vai um
1 + a  = b
1 + f  = 10 // <- vai um
1 + 10 = 11
```

Como você percebeu, operações "vai um" precisam de mais dígitos para representar um certo número. Compreender isto te permite entender como alguns tipos de dados são limitados e, por serem armazenados em computadores, essa limitação é representada no formato binário.

## Representação de dados na memória do computador

Como eu comentei antes, computadores armazenam tudo usando o formato binário. Então apenas 0s e 1s são efetivamente armazenados.

The easiest way to visualize how they are stored, is by imagining a big table with a single row and many columns (as many as storage capacity), where each column is a binary digit (bit).

A forma mais fácil de visualizar como estes dados são armazenados é imaginar uma grande tabela com uma única linha e várias colunas (tantas colunas quanto a capacidade de armazenamento), onde cada coluna representa um dígito binário (um bit).

A gente pode representar o nosso 20 decimal nesta tabela utilizando apenas 8 bits, fica assim:

<table><tbody>
<tr>
  <th>Posição (Endereço)</th>
  <td>0</td><td>1</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td><td>7</td>
</tr>
<tr>
  <th>Bit</th>
  <td>0</td><td>0</td><td>0</td><td>1</td><td>0</td><td>1</td><td>0</td><td>0</td>
</tr>
</tbody></table>

Um inteiro de 8 bits sem sinal (unsigned integer de 8 bits) é um número inteiro que pode ser representado somente com no máximo 8 dígitos binários. Então **0b11111111** (255 decimal) é o maior número que este integer pode armazenar. Somar 1 ao 255 decimal requer uma operação "vai um", que não pode ser representada com a mesma quantidade de dígitos (precisaria de 9 dígitos, no nosso caso).

Com isto em mente nós podemos facilmente entender o motivo de existir tantas formas de representar números e o que elas realmente são: uint8 é um inteiro de 8 bits sem sinal (0 a 255 decimal), uint16 é um inteiro de 16 bits sem sinal (0 a 65.535 decimal). Existe também uint32, uint64 e teoricamente limites maiores.

Inteiros com sinal, que também podem representar valores negativos, normalmente usam o último bit para determinar se o valor é positivo (último bit = 0) ou negativo (último bit = 1). Como você provavelmente deduziu, um inteiro com sinal é capaz de representar números bem menores que os inteiros sem sinal. Um inteiro com sinal de 8 bits é capaz de representar do decimal -128 até o decimal 127 apenas.

Aqui vai o decimal -20, representado como um inteiro de 8 bits e com sinal. Note como o último bit (endereço 0) está ligado (o valor é igual a 1). Este bit marca o número todo como negativo.

<table><tbody>
<tr>
  <th>Posição (Endereço)</th>
  <td>0</td><td>1</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td><td>7</td>
</tr>
<tr>
  <th>Bit</th>
  <td>1</td><td>0</td><td>0</td><td>1</td><td>0</td><td>1</td><td>0</td><td>0</td>
</tr>
</tbody></table>

Eu espero que até aqui tudo tenha feito sentido. Essa introdução é muito importante pra que você entenda como os computadores funcionam internamente. Só a partir daí você vai conseguir entender de forma confortável o que o PHP está fazendo por debaixo dos panos.

## Overflows Aritméticos

**Nota sobre a palavra Overflow:** a tradução de Overflow seria "transbordo", mas este termo é pouco utilizado. Eu vou me manter utilizando o termo em inglês: Overflow. O significado é equivalente ao de "transbordar" mesmo. Quando você enche um copo d’água além do limite, parte da água sai do copo: isto é um transbordo ou overflow.

A forma como os números são representados (8 bits, 16 bits...) determina a faixa de valores mínimos e máximos que podem ser representados. E isto ocorre por conta da forma como eles são armazenados em memória: adicionar 1 a um dígito binário 1 deveria causar uma operação "vai um" (carry) e, portanto, um outro bit seria necessário para fazer prefixo ao número atual.

Já que os números inteiros são bem definidinhos, não é possível confiar em operações "vai um" que ultrapassam seu limite. (Na verdade É POSSÍVEL, mas não recomendo nem para meu pior inimigo)

Vamos usar o tipo uint8 (inteiro de 8 bits sem sinal) como exemplo e representar seu número máximo - 1: o decimal 254.

<table><tbody>
<tr>
  <th>Posição (Endereço)</th>
  <td>0</td><td>1</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td><td>7</td>
</tr>
<tr>
  <th>Bit</th>
  <td>1</td><td>1</td><td>1</td><td>1</td><td>1</td><td>1</td><td>1</td><td>0</td>
</tr>
</tbody></table>

Aqui nós estamos bem perto do limite dos 8 bits (decimal 255). Se somarmos 1 a este número teremos o decimal 255 e a seguinte representação:

<table><tbody>
<tr>
  <th>Posição (Endereço)</th>
  <td>0</td><td>1</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td><td>7</td>
</tr>
<tr>
  <th>Bit</th>
  <td>1</td><td>1</td><td>1</td><td>1</td><td>1</td><td>1</td><td>1</td><td>1</td>
</tr>
</tbody></table>

Todos os bits estão ligados! Somar 1 a este número requer uma operação de "vai um" que não pode acontecer, porque não temos bits o suficiente: todos os 8 bits estão ligados! Isto gera uma coisa chamada **overflow**, que acontece toda vez que você tenta ir acima de um determinado limite. A operação binária 255 + 2 vai resultar em 1, e fica representada assim:

<table><tbody>
<tr>
  <th>Posição (Endereço)</th>
  <td>0</td><td>1</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td><td>7</td>
</tr>
<tr>
  <th>Bit</th>
  <td>0</td><td>0</td><td>0</td><td>0</td><td>0</td><td>0</td><td>0</td><td>1</td>
</tr>
</tbody></table>

Este comportamento não é aleatório! Existe toda uma base de cálculos, que não é relevante aqui, envolvida para determinar este valor.

## Números e strings binárias no PHP {#numeros-e-strings-binarias-no-php}

Ok, de volta ao PHP! Foi mal desviar tanto o assunto, mas foi necessário.

Eu espero que a partir deste momento os pontos já foram ligados na sua cabeça: números binários, como eles são armazenados, o que é um overflow, como o PHP representa números...

O decimal 20 representado como um inteiro no PHP pode ter dois formatos diferentes, dependendo da sua plataforma. A plataforma x86 o representa com 32 bits enquanto a plataforma x64 o representa com 64 bits, ambos com sinal (permite valores negativos). Nós bem sabemos que o decimal 20 pode ser representado num espaço bem mais curto, de 8 bits apenas, mas o PHP trata todo valor decimal como um inteiro de 32 ou 64 bits.

No PHP também existe o conceito de strings binárias, que podem ser convertidas e interpretadas utilizando as funções [pack()](https://www.php.net/manual/en/function.pack.php) and [unpack()](https://www.php.net/manual/en/function.unpack.php).

A maior diferença entre strings binárias e números no PHP é que strings binárias apenas armazenam dados, como um buffer. Já os inteiros no PHP (binários ou não) nos permite executar operações aritméticas neles como a soma, subtração e operações binárias (bitwise) como AND, OR e XOR.

## Binários: Inteiros ou Strings, qual usar no PHP?

Para transportar dados nós normalmente utilizamos strings binárias. Então ler um arquivo binário ou se comunicar por rede vai nos exigir utilizar as funções _pack()_ e _unpack()_ em strings binárias.

Operações como OR e XOR não são confiáveis quando executadas com strings, então nós devemos utilizá-las com inteiros.

## Depurando valores binários em PHP

Agora vem a parte legal! Vamos sujar as mãos e brincar um pouco com código PHP!
A primeira coisa que eu quero te mostrar é como visualizar os dados. Afinal a gente precisa entender o que estamos lidando.

### Visualizando representações binárias de números inteiros

Depurar inteiros é bem simples: a gente pode usar a função [sprintf()](https://www.php.net/manual/en/function.sprintf). A sua formatação é muito poderosa e nos permite identificar rapidamente o que os valores são.

Abaixo eu vou representar o decimal 20 como um inteiro de 8 bits em binário e como um byte hexadecimal.

```
<?php
// Decimal 20
$n = 20;

echo sprintf(‘%08b‘, $n) . "\n";
echo sprintf(‘%02X’, $n) . "\n";

// Saída:
00010100
14
```

O formato "%08b" apresenta a variável $n no formato binário (b) utilizando 8 dígitos (08).

O formato "%02X" representa a variável $n no formato hexadecimal (X) e utilizando 2 dígitos (02).

### Visualizando strings binárias

Enquanto os inteiros no PHP são sempre de 32 ou 64 bits, uma string pode ocupar tanta memória quanto seu conteúdo requer. Para visualizar seu valor nós precisamos interpretar cada byte.

A nossa sorte é que no PHP strings podem ter seus caracteres acessados como fazemos com arrays, então cada posição da string aponta para um char de 1 byte. Abaixo mostro um exemplo de como estes caracteres podem ser acessados:

```
<?php
$str = ‘thephp.website’;

echo $str[3];
echo $str[4];
echo $str[5];

// saída:
php
```

Confiando que cada char tem 1 byte, podemos facilmente chamar a função [ord()](https://www.php.net/manual/en/function.ord) para converter este char em um inteiro de 1 byte (8 bits). Mais ou menos assim:

```
<?php
$str = ‘thephp.website’;

$p = ord($str[3]);
$s = ord($str[4]);
$t = ord($str[5]);

echo sprintf(‘%02X %02X %02X’, $p, $s, $t);
// Saída:
70 68 70
```

A gente pode ver que não estamos nos confundindo ao verificar este mesmo valor utilizando a ferramenta hexdump:

```
$ echo ‘php’ | hexdump
// Saída
0000000 70 68 70 ...
```

A primeira coluna mostra o endereço apenas, e a partir da segunda coluna nós vemos os valores hexadecimais representando os caracteres ‘p’, ‘h’ e ‘p’.

Nós também podemos utilizar as funções [pack()](https://www.php.net/manual/en/function.pack.php) e [unpack()](https://www.php.net/manual/en/function.unpack.php) quando lidamos com strings binárias e eu tenho um ótimo exemplo pra você bem aqui!!

Digamos que você queira ler um arquivo JPEG para coletar alguns metadados (como o EXIF, por exemplo). A gente pode abrir o arquivo utilizando o modo de leitura binário. Vamos fazer isto imediatamente e ler os primeiros 2 bytes:

```
<?php

$h = fopen(arquivo.jpeg’, ‘rb’);

// Ler 2 bytes
$soi = fread($h, 2);
```

Para coletar estes valores num array de números inteiros a gente pode usar a função unpack desta forma:

```
$ints = unpack(‘C*’, $soi);

var_dump($ints);
// Saída
array(2) {
  [1] => int(-1)
  [2] => int(-40)
}

echo sprintf(‘%02X’, $ints[1]);
echo sprintf(‘%02X’, $ints[2]);
// Saída
FFD8
```

Note que o formato "C" que passamos para a função unpack() vai interpretar caracteres na string $soi como números inteiros de 8 bit sem sinal. O modificador "*" faz com que o unpack() extraia todos os caracteres restantes na string da mesma forma.

## Operações Binárias (Bitwise)

O PHP implementa todas as operações binárias que você possa querer. Elas são implementadas como expressões e seus resultados são descritos abaixo:

<table>
<thead>
  <th>Código PHP</th><th>Nome</th><th>Descrição</th>
</thead>
<tbody>
  <tr>
    <td>$x | $y</td><td>Ou inclusivo (Or)</td><td>Um valor com os bits ligados em $x e $y ao mesmo tempo</td>
  </tr>
  <tr>
    <td>$x ^ $y</td><td>Ou exclusivo (Or)</td><td>Um valor com os bits ligados em $x ou $y, mas nunca nos dois ao mesmo tempo</td>
  </tr>
  <tr>
    <td>$x & $y</td><td>E (AND)</td><td>Um valor somente com os bits ligados em $x e $y ao mesmo tempo</td>
  </tr>
  <tr>
    <td>~$x</td><td>Negar (Not)</td><td>Nega todos os bits em $x. O que é 1 vira 0, e o que é 0 vira 1</td>
  </tr>
  <tr>
    <td>$x << $y</td><td>Deslocamento a esquerda (Left shift)</td><td>Desloca os bits de $x para a esquerda $y vezes</td>
  </tr>
  <tr>
    <td>$x >> $y</td><td>Deslocamento a direita (Right shift)</td><td>Desloca os bits de $x para a direita $y vezes</td>
  </tr>
</tbody>
</table>

Eu vou explicar uma por uma como estas operações funcionam, não se preocupe!
Vamos assumir que _$x = 0x20_ e _$y = 0x30_. Os exemplos abaixo vão os apresentar usando a notação binária para esclarecer as coisas.

### Como o Ou Inclusivo (Or) funciona ($x | $y)

A operação Ou inclusivo vai produzir um resultado que pega todos os bits ligados das duas variáveis passadas. Então a operação $x | $y deve retornar o valor 0x30. Veja o que tá acontecendo abaixo:

```
// 1 | 1 = 1
// 1 | 0 = 1
// 0 | 0 = 0

0b00100000 // $x = 0x20
0b00110000 // $y = 0x30
OR ------- // $x | $y
0b00110000 // 0x30
```

Repare bem: da esquerda para a direita, o sexto bit de $x  estava ligado (valor = 1) enquanto os bits 5 e 6 de $y também estavam ligados. O resultado une os dois e gera um valor com os bits 5 e 6 ligados: 0x30.

### Como o Ou exclusivo (Xor) funciona ($x ^ $y)

O Ou exclusivo (também conhecido como Xor) captura bits que estejam ligados em apenas um dos lados da operação. Então o resultado de $x ^ $y é 0x10. Veja o que acontece nesta operação:

```
// 1 ^ 1 = 0
// 1 ^ 0 = 1
// 0 ^ 0 = 0

0b00100000 // $x = 0x20
0b00110000 // $y = 0x30
XOR ------ // $x ^ $y
0b00010000 // 0x10
```

### Como o E (And) funciona ($x & $y)

A operação E é bem mais simples de entender. Cada bit, dos dois lados, são comparados e apenas os valores que são iguais serão coletados para o resultado.

O resultado de $x & $y é 0x20, olha o porquê:

```
// 1 & 1 = 1
// 1 & 0 = 0
// 0 & 0 = 0

0b00100000 // $x = 0x20
0b00110000 // $y = 0x30
AND ------ // $x & $y
0b00100000 // 0x20
```

### Como a operação Negar (Not) funciona (~$x)

A operação Negar requer apenas um operando e simplesmente inverte todos os bits. Ela transforma todos bits que eram 0 em 1, e todos os bits que eram 1 em 0. Veja:

```
// ~1 = 0
// ~0 = 1

0b00100000 // $x = 0x20
NOT ------ // ~$x
0b11011111 // 0xDF
```

Se você rodou esta operação no PHP e decidiu depurar o resultado utilizando sprintf() você provavelmente recebeu um número bem mais longo, né? Eu vou te explicar o que aconteceu e como corrigir abaixo na seção ["Corrigindo Inteiros"](#corrigindo-inteiros).
Deslocamentos à esquerda e à direita (Left e Right shifts) ($x << $n, $x >> $n)
Deslocar bits é a mesma coisa que multiplicar ou dividir seus números por múltiplos de dois. O que esta operação faz é que todos os bits andem $n vezes para a esquerda ou direita.

Eu vou pegar um número binário menor para representar esta operação, só pra deixar a leitura mais facilitada. Vamos pegar $x = 0b0010 como exemplo! Se a deslocarmos uma vez para a esquerda, aquele bit 1 se move um passo para a esquerda:

```
0b0010 // $x
$x = $x << 1
0b0100
```

A mesma coisa acontece com o deslocamento a direita. Agora que $x = 0b0100 vamos deslocá-la para a direita duas vezes:

```
0b0100 // $x
$x = $x >> 2
0b0001
```

No fim das contas, deslocar um número $n vezes para a esquerda é o mesmo que multiplicá-lo por 2 $n vezes, e deslocá-lo $n vezes para a direita é equivalente a dividir por 2 $n vezes.

## O que é uma Máscara? (bitmask)

Tem várias coisas interessantes que a gente pode fazer com estas operações e outras técnicas. Uma ótima técnica para sempre trazer consigo é utilizar máscaras (bitmasks).

Uma máscara é apenas um binário que você escolhe, escrito para extrair uma informação específica de acordo com a sua necessidade.

Por exemplo, vamos tomar a ideia de que um inteiro de 8 bits com sinal é positivo quando o último bit está desligado (valor = 0) e é negativo quando o último bit está ligado (valor = 1). Eu então te pergunto: o número 0x20 é positivo ou negativo? E o 0x81?

Pra responder essas perguntas nós podemos criar um byte que liga apenas o último bit (0b10000000, equivalente a 0x80) e utilizar a operação E (AND) entre este valor e 0x20. Se o resultado for 0x80 (0b10000000, a nossa máscara) então o número é negativo, se não o número é positivo:

```
// 0x80 === 0b10000000 (bitmask)
// 0x20 === 0b00100000
// 0x81 === 0b10000001

0x20 & 0x80 === 0x80 // false
0x81 & 0x80 === 0x80 // true
```

Isto é muito útil quando você quer lidar com flags. Você pode inclusive ver exemplos de utilização no próprio PHP: [the error reporting flags](https://www.php.net/manual/pt_BR/function.error-reporting.php).

É possível escolher quais tipos de são reportados fazendo algo assim:

```
error_reporting(E_WARNING | E_NOTICE);
```

O que tá acontecendo aqui? Bom, vamos verificar os valores que utilizamos:

```
0b00000010 (0x02) E_WARNING
0b00001000 (0x08) E_NOTICE
OR -------
0b00001010 (0x0A)
```

Então sempre que o PHP ver que um Notice poderia ser reportado, vai verificar algo assim:

```
// error reporting que definimos antes
$e_level = 0x0A;

// Pode lançar um notice?
if ($e_level & E_NOTICE === E_NOTICE)
   // Lançar notice
```

E você vai ver isto em tudo quanto é lugar! Arquivos binários, processadores e todo tipo de computação de baixo nível!

## Corrigindo Inteiros {#corrigindo-inteiros}

No PHP tem algo muito particular quando lidamos com números binários: nossos inteiros são de 32 ou 64 bits. Isto significa que várias vezes vamos precisar corrigir os valores para confiar em nossos cálculos.

Por exemplo, a seguinte operação numa máquina de 64 bits vai nos retornar um número bem estranho (apesar de ser o resultado correto):

```
echo sprintf(
  ‘0b%08b’,
  ~0x20
);

// Expectativa
0b11011111
// Realidade
0b1111111111111111111111111111111111111111111111111111111111011111
```

Diabé isso!? Veja, ao negar aquele inteiro 0x20 nós transformamos todos os bits zero e os transformamos em 1s. Adivinha o que costumava ser zero? Exato, todos os outros 56 bits à esquerda que nós ignoramos antes!

Novamente, isto acontece porque os inteiros do PHP têm 32 ou 64 bits, não importa o valor que você colocar dentro deles!

Mas o código ainda funciona como esperado. Por exemplo, a operação `~0x20 & 0b11011111 === 0b11011111` resulta em _bool(true)_. Mas tenha sempre em mente que estes bits à esquerda estão ali, ou você pode acabar tendo comportamentos inesperados no seu código.

Para resolver este problema, você pode corrigir os inteiros aplicando uma máscara (bitmask) que limita os zeros. Por exemplo, para normalizar ~0x20 como um inteiro de 8 bits a gente precisa utilizar uma operação E (AND) com o 0xFF (0b11111111) de forma que todos os 56 bits restantes vão ser desligados (valor = 0).

```
~0x20 & 0xFF
-> 0b11011111
```

**Prestenção ein!** Nunca se esqueça de o que você está armazenando em suas variáveis, ou sua aplicação pode acabar com bugs bem difíceis de se encontrar. Por exemplo, vamos ver o que acontece quando deslocamos à direita o valor acima sem utilizar uma máscara.

```
~0x20 & 0xFF
-> 0b11011111

0b11011111 >> 2
-> 0b00110111 // esperado

(~0x20 & 0xFF) >> 2
-> 0b00110111 // esperado

(~0x20 >> 2) & 0xFF
-> 0b11110111 // esperado?
```

Só para esclarecer: do ponto de vista do PHP este comportamento É esperado, porque você claramente está lidando com um inteiro de 64 bits aqui! Você precisa sempre deixar bem explícito o que o SEU programa precisa.

**Dica de mestre:** você pode escapar de erros bestas como estes ao [escrever seu código com TDD](https://thephp.website/br/edicao/tdd-com-php-na-vida-real/).

## Enfim, binário é maneirão

Eu espero que você tenha curtido ler tanto quanto eu curti escrever este post. E mais importante: eu espero que este conhecimento te permita se aventurar por este maravilhoso mundo de dados binários.

Com estas ferramentas em mão, todo o resto é apenas questão de achar a documentação correta sobre como arquivos ou protocolos binários se comportam. Tudo é uma sequência de binários no fim das contas.


Eu recomendo fortemente que você dê uma olhada na especificação dos formatos PDF ou EXIF (metadados de imagem). Talvez você até queira brincar com a sua própria implementação do [formato de serialização MessagePack](https://thephp.website/en/issue/messagepack-vs-json-benchmark/) ou talvez Avro, Protobuf... Infinitas possibilidades!

Como você deve ter reparado este arquivo me levou um tempão pra escrever. Se você quiser recompensar o esforço, dá aquela compartilhada e salva nos favoritos pra voltar aqui sempre que tiver alguma dúvida sobre este tópico.

Talvez em breve eu volte com algumas coisas mais práticas sobre lidar com binários! :)

Valeu!

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
  "headline": "Operações binárias (bitwise) com PHP",
  "description": "Recentemente eu trabalhei em diferentes projetos que me forçaram a usar bastante operações com binários em PHP. De ler arquivos a emular processadores, este é um conhecimento interessantíssimo e muito útil.",
  "image": [
    "{{ $page->getBaseUrl() }}/assets/images/posts/20-bitwise-php/cover-640.webp"
   ],
  "datePublished": "2021-02-24T00:00:00+08:00",
  "dateModified": "2021-02-24T00:00:00+08:00",
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
