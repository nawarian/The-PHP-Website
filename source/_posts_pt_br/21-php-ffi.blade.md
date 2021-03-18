---
isFeatured: true
lang: pt-br
slug: php-ffi
title: 'Guia completo: FFI em PHP'
category: guia 
createdAt: 2021-03-18
sitemap:
  lastModified: 2021-03-18
image:
  url: /assets/images/posts/21-php-ffi-640.webp
  alt: 'Uma figura humana pintada com zeros e uns.'
tags:
  - ffi
  - binário
  - raylib
  - jogos
meta:
  description:
    Ao utilizar FFI no seu programa PHP você será capaz de
    utilizar bibliotecas escritas em C, Rust, Golang ou
    quaisquer outras linguagens capazes de produzir uma ABI.
  twitter:
    card: summary
    site: '@nawarian'
---

[Read in English](/en/issue/php-ffi/)

Antes de qualquer coisa, eu quero te dizer que eu comecei uma pequena série de vídeos onde eu implementei uma biblioteca em PHP para usar a biblioteca raylib (escrita em C) usando FFI. Você pode ver no vídeo abaixo ou [neste link para o YouTube](https://youtu.be/wPbnjcvW-Tk).

<iframe style="margin: auto;" id="lbry-iframe" width="560" height="315" src="https://lbry.tv/$/embed/php_ffi_ola_mundo/a6959afa7affbba52bb31e68fbfbd222af129261?r=HRN5EEjNyXcykeZ8RjgLX697DuZvpA7g" allowfullscreen></iframe><br />

## O que é FFI e pra quê serve?

FFI ou [Foreign Function Interface](https://en.wikipedia.org/wiki/Foreign_function_interface) é uma técnica que permite programas utilizarem bibliotecas escritas em diferentes linguagens de programação. É bem mais rápido que usar RPC ou APIs porque o programa não vai se comunicar através de rede e, em vez disso, faz interface direta com a biblioteca.

Para ser mais direto: ao utilizar FFI no seu programa PHP você será capaz de utilizar bibliotecas escritas em C, Rust, Golang ou quaisquer outras linguagens capazes de produzir uma ABI.

É importante notar que você vai conseguir se comunicar com bibliotecas, não entre dois programas diferentes. Para comunicar dois programas você ainda vai precisar de algum mecanismo de comunicação em tempo real, e FFI não te ajuda em nada com isso!

Ao utilizar FFI no PHP você será capaz de usar qualquer shared object que quiser em seu projeto: .dll no Windows, .so no Linux ou .dylib no MacOS.

Com isso você tem a oportunidade de sair da [Máquina Virtual do PHP (Zend VM)](https://thephp.website/br/edicao/php-8-jit/) e escrever quase qualquer coisa que você gostaria usando PHP. Utilizar bibliotecas como raylib ou libui não vai te obrigar a utilizar uma extensão em C (como nós fizemos [neste post sobre como desenvolver jogos em PHP usando a extensão raylib](https://thephp.website/br/edicao/jogos-em-php/)).

## FFI vai tornar meu código mais rápido?

Você talvez esteja imaginando que já que o FFI permite utilizar código escrito originalmente em C, o seu programa potencialmente será mais rápido do que seria em PHP. A linha de raciocínio não está necessariamente errada, mas você precisa levar em consideração que linguagens de programação não fazem mágica: elas fazem o que nós as comandamos fazer.

**Em termos de tempo de CPU, chamar funções externas a partir do PHP utilizando FFI pode te custar duas vezes mais do que realizar a mesma operação em PHP puro.** Isto acontece porque a máquina virtual do PHP já é bem otimizada e fazer interface com código externo requer um processo de tradução que vai adicionar um certo custo de processamento.

Isso é normal e todas linguagens que suportam FFI que eu vi até agora vão performar um pouquinho pior quando utilizam FFI.

**Mas você pode otimizar o consumo de memória!** Como você pôde ver no meu post sobre [Operações Binárias em PHP](https://thephp.website/br/edicao/operacoes-bitwise-php/#php-melhor-linguagem), cada variável do PHP tem um tipo interno zval e ele faz várias coisas pra tonar a vida do PHP mais fácil, como representar todo integer em PHP com o tipo INT64. Então o valor `0x10` teria de ser armazenado como `0x0000000000000010` em PHP (e todos os outros membros de zval terão seus ponteiros alocados).

Então uma boa prática é tentar encontrar o equilíbrio entre processar coisas usando PHP e utilizar o FFI para lidar com objetos na memória. Desta forma você consegue otimizar o consumo de memória, que pode ou não impactar no seu tempo de CPU.

## FFI ou Extensões em C, o que eu devo utilizar?

FFI normalmente é utilizado para prototipação: você dá os primeiros passos com FFI e depois migra o código para uma extensão escrita em C.

Eu acho que se o seu código não se importa muito com performance (improvável, mas pode ser…) é de boa usar o FFI só para ampliar a capacidade do PHP. [Mas não se esqueça que FFIs em PHP ainda são experimentais e você pode encontrar bugs ou mudanças na API podem acontecer de vez em quando](https://www.php.net/manual/en/intro.ffi.php).

Extensões em C deveriam normalmente ser escritas em código C, uma barreira para muitas pessoas acostumadas com PHP. Mas estas extensões se integram à Máquina Virtual do PHP, e por conta disso tendem a ser bem mais rápidas porque chamam código em C diretamente do C (nenhuma tradução é necessária) e mapeiam apenas o código que vai interfacear com o usuário final da extensão.

Extensões são compiladas contra uma versão específica do PHP, e isso cria uma dependência bem chatinha que pode te impedir de atualizar a versão do PHP, por exemplo. Se você tiver disponibilidade pra atualizar a extensão por si e seguir o processo de integração que a comunidade propõe, menos mal. Mas ainda assim vai te custar alguns dias.

FFIs sempre vão funcionar direto e não vão te impedir de atualizar a versão do PHP porque a [extensão FFI é parte do core do PHP](https://github.com/php/php-src/tree/master/ext/ffi).

## Começando com FFI: vamos construir uma janela nativa usando a biblioteca raylib

Uma coisa que o PHP sozinho definitivamente não consegue fazer é manipular janelas nativas no sistema operacional. Existem algumas extensões como o PHP-GTK e [a extensão raylib que nós vimos antes](https://thephp.website/br/edicao/jogos-em-php/), outra opção é usar FFI.

Eu vou escolher a [Raylib](https://www.raylib.com/) como exemplo porque a sua interface é bem simplificada e gostosa de se trabalhar.

### Instalando o shared object da raylib (biblioteca)

Para quem usa mac isto deveria ser bem simples utilizando o HomeBew:

```
$ brew install raylib
```

Existem alguns guias completos de como instalar em outros sistemas. Aqui você encontra guias sobre [como instalar no Windows](https://github.com/raysan5/raylib/wiki/Working-on-Windows) e [como instalar no Linux](https://github.com/raysan5/raylib/wiki/Working-on-GNU-Linux).

Depois de instalar tudo você deverá ter um shared object disponível no seu sistema. No MacOS você pode ver o arquivo `libraylib.dylib` dentro do diretório `/usr/local/Cellar/raylib/<versão>/lib`:

```
$ ls -la /usr/local/Cellar/raylib/3.5.0/lib
cmake			libraylib.351.dylib	libraylib.dylib
libraylib.3.5.0.dylib	libraylib.a		pkgconfig
```

No Windows você vai se preocupar em encontrar o arquivo `.dll` e no GNU Linux você precisa encontrar o arquivo `.so`.

### Vamos primeiro prototipar nosso programa em C

A forma mais fácil de entender se o FFI está funcionando corretamente em PHP é entender se a coisa funciona em C em primeiro lugar. Faz sentido né?

Então a primeira coisa que vamos fazer é construir um programa em C que utilize a raylib e que construa a nossa janela. Vamos criar um arquivo `hello_raylib.c` com o seguinte conteúdo:

```
#include "raylib.h"

int main(void)
{
  Color white = { 255, 255, 255, 255 };
  Color red = { 255, 0, 0, 255 };

  InitWindow(
    800,
    600,
    "Hello raylib from C"
  );

  while (
    !WindowShouldClose()
  ) {
    ClearBackground(white);

    BeginDrawing();
      DrawText(
        "Hello raylib!",
        400,
        300,
        20,
        red
      );
    EndDrawing();
  }

  CloseWindow();
}
```
O código acima deve criar uma janela com dimensões 800x600 e o texto "Hello raylib from C" na barra de título. Dentro desta janela o texto “Hello raylib!” em cor vermelha deverá aparecer com origem no meio da janela.

Vamos compilar e rodar o programa acima:

```
$ gcc -o hello_raylib \
  hello_raylib.c -lraylib
$ ./hello_raylib
```

**Repare:** utilize o compilador C disponível em sua plataforma. No meu caso eu utilizo o `clang` mas deveria ser mais ou menos a mesma coisa.

Abaixo o resultado esperado.

<figure style="text-align: center">
  <a href="/assets/images/posts/21-php-ffi/raylib-window-c.png" target="_blank">
    <img src="/assets/images/posts/21-php-ffi/raylib-window-c.png" alt="Uma janela nativa com dimensões 800 por 600 e o título 'Hello raylib from C' apresentando um texto em cor vermelha que diz 'Hello raylib!'" />
  </a>
  <figcaption>
    Uma janela nativa com dimensões 800 por 600 e o título "Hello raylib from C" apresentando um texto em cor vermelha que diz "Hello raylib!"
  </figcaption>
</figure>

### Agora com PHP! Vamos criar nosso arquivo de cabeçalho (header)

Para permitir que o PHP se comunique com o C (ou outras linguagens), nós primeiro precisamos criar uma interface. Em C esta interface é representada por arquivos de cabeçalho. Esta é exatamente a razão pela qual a maioria dos arquivos `.c` têm um correspondente `.h` no projeto: o arquivo de cabeçalho indica quais objetos e assinaturas de funções existem.


Já que nós queremos referenciar o `libraylib.dylib` a primeira linha do nosso cabeçålho deve conter o seguinte define, específico para FFI. Então vamos começar escrevendo o nosso `raylib.h` que vai interfacear com o código PHP:

```
#define FFI_LIB "libraylib.dylib"
```

**Repare:** o arquivo referenciado pode mudar de acordo com seu sistema operacional.

A Raylib tem várias funções, e [você pode verificar cada uma na cheatsheet oficial](https://www.raylib.com/cheatsheet/cheatsheet.html). Mas nós não precisamos importar todas as funções. Na verdade eu recomendo que você importe apenas as funções necessárias para o seu programa funcionar. No nosso caso nós precisamos de apenas 7 funções:

```
#define FFI_LIB "libraylib.dylib"

void InitWindow(
  int width,
  int height,
  const char *title
);
bool WindowShouldClose(void);
void ClearBackground(
  Color color
);
void BeginDrawing(void);
void DrawText(
  const char *text,
  int x,
  int y,
  int size,
  Color color
);
void EndDrawing(void);
void CloseWindow(void);
```

Repare que algumas assinaturas de função requerem tipos muito específicos que são oferecidos pela Raylib. As funções `ClearBackground` e `DrawText` exigem um argumento do tipo `Color`, que nós também precisamos importar. Então vamos adicionar ao nosso arquivo de cabeçalho:

```
#define FFI_LIB "libraylib.dylib"

typedef struct Color {
  unsigned char r;
  unsigned char g;
  unsigned char b;
  unsigned char a;
} Color;

void InitWindow(int width, int height, const char *title);
// ...
```

Nosso arquivo **raylib.h** agora está pronto para ser utilizado dentro do PHP.

### Carregando o cabeçalho no PHP

Agora que nós temos um arquivo de cabeçalho nós podemos importá-lo utilizando a função [FFI::load()](https://www.php.net/manual/en/ffi.load.php) desta maneira:

```
<?php

$ffi = FFI::load(
  __DIR__ . '/raylib.h'
);
```

Com este objeto `$ffi` nós podemos agora imitar o código em C que escrevemos antes. Vamos construir as variáveis `white` e `red` do tipo `Color`:

```
<?php

$ffi = FFI::load(__DIR__ . '/raylib.h');

$white = $ffi->new('Color');
$white->r = 255;
$white->g = 255;
$white->b = 255;
$white->a = 255;

$red = $ffi->new('Color');
$red->r = 255;
$red->a = 255;
```

Por padrão todos os campos do struct serão inicializados com um valor zero. No caso do `unsigned char` (que varia entre 0 e 255) o valor zero é um inteiro `0`.

Agora nós podemos facilmente construir a nossa janela e desenhar na tela:

```
<?php

$ffi = FFI::load(__DIR__ . '/raylib.h');

// ...

$ffi->InitWindow(
  800,
  600,
  "Hello raylib from PHP"
);

while (
  !$ffi->WindowShouldClose()
) {
  $ffi->ClearBackground(
    $white
  );

  $ffi->BeginDrawing();
    $ffi->DrawText(
      "Hello raylib!",
      400,
      300,
      20,
      $red
    );
  $ffi->EndDrawing();
}

$ffi->CloseWindow();
```

### Temos uma janela usando a raylib no PHP!

Como você provavelmente percebeu, todas as funções em C definidas em `raylib.h` podem ser utilizadas pelo PHP se utilizamos o objeto `$ffi` para referenciá-las. As variáveis em C são mapeadas para PHP e vice-versa.

O nosso arquivo PHP final e seu resultado ficaram assim:

```
<?php

$ffi = FFI::load(__DIR__ . '/raylib.h');

$white = $ffi->new('Color');
$white->r = 255;
$white->g = 255;
$white->b = 255;
$white->a = 255;

$red = $ffi->new('Color');
$red->r = 255;
$red->a = 255;

$ffi->InitWindow(800, 600, "Hello raylib from PHP");
while (!$ffi->WindowShouldClose()) {
  $ffi->ClearBackground($white);

  $ffi->BeginDrawing();
    $ffi->DrawText("Hello raylib!", 400, 300, 20, $red);
  $ffi->EndDrawing();
}

$ffi->CloseWindow();
```

<figure style="text-align: center">
  <a href="/assets/images/posts/21-php-ffi/raylib-window-php-ffi.png" target="_blank">
    <img src="/assets/images/posts/21-php-ffi/raylib-window-php-ffi.png" alt="Uma janela nativa com dimensões 800 por 600 e o título 'Hello raylib from PHP' apresentando um texto em cor vermelha que diz 'Hello raylib!'" />
  </a>
  <figcaption>
    Uma janela nativa com dimensões 800 por 600 e o título "Hello raylib from PHP" apresentando um texto em cor vermelha que diz "Hello raylib!"
  </figcaption>
</figure>

## Problemas comuns com FFI e como resolvê-los

Eu estive brincando com o FFI para tentar construir bindings bacanas para a Raylib em PHP e encontrei alguns problemas no caminho. Saber sobre estes problemas e como corrigi-los talvez possa lhe ser útil.

A minha maior dica é: não misture o código da sua aplicação com código FFI. Extraia o FFI para uma biblioteca independente e adicione-a ao seu projeto utilizando o composer. Isto não vai resolver a maioria dos seus problemas, mas com certeza vai os isolar e tornar muito fácil a testagem.

### FFI pode ser difícil de testar

No caso da Raylib em específico a gente não consegue testar muita coisa. Principalmente porque a raylib manipula janelas nativas e o PHP não tem uma forma fácil de fazer assertions deste tipo.

Então tenha em mente que se você estiver escrevendo algo realmente fora do escopo normal do PHP, você vai precisar de outras ferramentas para conduzir seus testes. Tenha certeza, portanto, que estas ferramentas também rodam em outras plataformas.

Por exemplo, é possível capturar o PID de uma janela procurando por seu título com xorg, e eu sei que de alguma forma a Windows API também nos permite fazer isso. **Se você quer testar, você provavelmente vai precisar utilizar outras ferramentas que não o PHP para testar sua aplicação.**

Também é importante lembrar que os testes não vão necessariamente agregar valor em todo lugar na sua aplicação. [Eu utilizo testes como uma ferramenta de aprendizado para que eu possa ter um ambiente seguro para testar novos conceitos aos poucos](https://thephp.website/br/edicao/tdl-aprendizado-guiado-por-testes/) sem me importar muito sobre todas as dependências de uma vez e, infelizmente, a maioria dos frameworks PHP não me ajudaram muito enquanto estive trabalhando com a raylib. A minha solução para este caso é criar diferentes arquivos PHP que deveriam fazer uma única coisa, exatamente como casos de teste.

### É difícil fazer análise estática

Eu não achei uma forma perfeita de resolver este problema. Ferramentas de análise estática como o [psalm](https://psalm.dev/) ficam doidinhas com código FFI.

De volta ao snippet `$white` e `$red` vamos ver o motivo:

```
$white = $ffi->new('Color');
$white->r = 255;
$white->g = 255;
$white->b = 255;
$white->a = 255;
```

Se você verificar a [assinatura de FFI::new()](https://www.php.net/manual/en/ffi.new.php) vai sacar que ele retorna `FFI\CData` ou `null`. Este tipo CData é um objeto que deveria conter todos os membros do struct referenciado.

Até onde eu sei o psalm não tem uma forma fácil de anotar que a variável `$white` contém quatro campos do tipo integer: `$r`, `$g`, `$b` e `$a`. O psalm sequer vai conseguir saber que eles existem porque, bem, eles foram escritos em C nalgum outro lugar!

Então o ideal é que você abstraia a lógica de FFI em algum tipo de classe Facade ou Adapter, que você vai prometer de pé juntinho que vai cobrir com testes o máximo que puder, e então pode dizer ao psalm para ignorar esta classe enquanto estiver conduzindo a análise estática.

Esta classe Facade/Adapter deverá mapear valores PHP (primitivos ou objetos) em CData e tomar conta de chamar as funções em C para você.

Assim você acaba construindo mais ou menos uma biblioteca em PHP, que é o ideal se você parar pra pensar. Desta forma você evita que o código de produção fique poluído com lógicas específicas do FFI e as coisas ficarão naturalmente testáveis na sua aplicação.

### Mantenha a sua biblioteca atualizada

Um grande benefício de utilizar FFI em vez de extensões do PHP é que você não vai precisar atualizar o seu código C a cada nova versão do PHP. Mas você ainda precisa gerenciar suas versões da biblioteca em C.

Eu recomendo que você aprenda sobre o sistema de versionamento da biblioteca original e faça releases na sua biblioteca em PHP seguindo a mesma regra, exceto para versões **patch**. Então versões **major** e **minor** sempre vão bater com a versão original da biblioteca em C, enquanto você terá a liberdade de aumentar a versão **patch** sempre que você corrigir bugs e coisas do tipo.

Isto naturalmente vai te forçar a respeitar 100% as interfaces da biblioteca C original. Mas te deixa livre para distribuir correções de segurança e de bugs que possam existir tanto na biblioteca C quanto na sua própria biblioteca em PHP.

### O problema de ser multiplataforma

O PHP é multiplataforma. Quem utiliza PHP espera que todas as bibliotecas sejam multiplataforma também! Manter as coisas assim pode ser um pouco complicado quando utilizamos código FFI.

De volta ao exemplo da raylib, importar aquele shared object nos força a escolher por nome de arquivo: `raylib.so` (GNU Linux), `libraylib.dylib` (MacOS) ou `raylib.dll` (Windows). Importe o arquivo errado e a sua biblioteca simplesmente não vai funcionar!

Você pode escrever diferentes arquivos de cabeçalho, específicos para a sua plataforma. Isto vai criar muita duplicação de  código mas ajuda um pouco.

Outra opção é utilizar o [FFI::cdef()](https://www.php.net/manual/en/ffi.cdef.php) para carregar as assinaturas de função. Este método é bem semelhante ao FFI::load() mas espera uma string em vez de um caminho de arquivo. Neste caso você pode escolher o caminho do seu shared object em tempo de execução.

Você consegue detectar o Sistema Operacional que está rodando o seu código PHP chamando a função [php_uname()](https://www.php.net/manual/en/function.php-uname.php). Evite utilizar a constante `PHP_OS`: ela contém o sistema operacional que compilou o seu binário PHP, que em alguns casos pode não ser o mesmo que está efetivamente rodando o seu código.

Por último, mas não menos importante, considere que algumas bibliotecas simplesmente não são multiplataforma. Postá-las pra PHP pode ser bem frustrante pra quem utiliza e, se você decidir portar esta biblioteca mesmo assim, por favor considere lançar exceções em sistemas operacionais aos quais você não oferece suporte: isto vai dizer ao usuário final logo de cara quais são os problemas.

### Existem bugs na própria extensão FFI

Lembre-se: FFI ainda é experimental no PHP! Você pode encontrar bugs inesperados a qualquer momento!

Sempre que você encontrar algum comportamento super estranho na sua integração FFI, sempre crie um arquivo em C que seja equivalente e veja seu comportamento antes de duvidar do que o FFI está fazendo.

Se a suspeita estiver correta e for realmente um bug, aproveite e [crie um bug ticket para o time do PHP](https://bugs.php.net/) (em inglês!). Eu não sei se o time vai ficar feliz pelo seu bug, mas com certeza você estará ajudando a comunidade a crescer.

Recentemente eu encontrei um e estou inclusive tentando implementar uma correção por conta própria, vai ser um projetinho bacana e potencialmente um post futuro. Então se liga aí!

## Concluindo

Eu fiquei bem animado ao trabalhar com FFI e espero que este post te ajude a começar a brincar com FFI também!

De pouco em pouco eu estou ficando mais acostumado com código de baixo nível e FFI tem sido uma ótima oportunidade para eu programar diferentes casos de uso (como desenvolvimento de games ou processamento áudio) numa linguagem que eu amo (PHP).

Lembre-se sempre que o PHP é uma linguagem de código aberto e que sua comunidade depende da contribuição de pessoas como você. Você pode utilizar seu conhecimento para devolver a comunidade ao reportar bugs, corrigi-los, complementar a documentação com coisas que você encontrou no caminho ou escrevendo artigos como este aqui. O FFI é definitivamente uma área de conhecimento que precisa de mais artigos e vídeos para que não caia no esquecimento.

Enfim, te vejo na próxima. Se cuida aí!

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
  "headline": "Guia completo: FFI em PHP",
  "description": "Ao utilizar FFI no seu programa PHP você será capaz de utilizar bibliotecas escritas em C, Rust, Golang ou quaisquer outras linguagens capazes de produzir uma ABI.",
  "image": [
    "{{ $page->getBaseUrl() }}/assets/images/posts/21-php-ffi-640.webp"
   ],
  "datePublished": "2021-03-18T00:00:00+08:00",
  "dateModified": "2021-03-18T00:00:00+08:00",
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
