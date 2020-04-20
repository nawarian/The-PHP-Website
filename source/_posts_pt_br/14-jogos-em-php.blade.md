---
slug: games-with-php
lang: pt-br
title: O jogo da cobrinha feito em PHP (com Raylib)
category: guides
createdAt: 2020-04-20
sitemap:
lastModified: 2020-04-20
image:
  url: /assets/images/posts/14-snake-640.webp
  alt: 'Uma cobra colorida olhando para a câmera.'
tags:
  - jogos
  - extensão
  - curiosidade
meta:
  description:
    Eu vou te mostrar como é o código e as ferramentas que usei!
    Espero que isso tome sua atenção suficientemente para vermos
    esta extensão ganhar tração.
twitter:
  card: summary
  site: '@nawarian'
---

Sim, você leu certo!

Um jogo. Escrito na linguagem PHP.

Antes de eu te mostrar o código em si, gostaria de mostrar o
resultado! Não está bem acabado, então abaixemos as expectativas
por agora. Eu só queria montar uma POC boa o suficiente pra mostrar
aqui 😬

Você pode ver o gameplay no vídeo abaixo.

<iframe src="https://player.vimeo.com/video/406784115" width="100%" height="400" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>

Massa, né!? E isso é só uma POC, mas com o que já existe nesta
extensão você já pode brincar com diferentes texturas, audios e
etc..

Eu vou te mostrar como ficou o código e quais ferramentas eu utilizei!
Espero que isso tome sua atenção suficientemente para vermos
esta extensão ganhar tração.
 
Antes de qualquer coisa, deixa eu te falar um pouco sobre a raylib.

## Raylib

Escrita na linguagem C, Raylib é definida como "uma biblioteca
simples e de fácil utilização para curtir a programação de jogos".

Ela oferece funções muito simplistas para manipular vídeo, áudio,
ler entradas de teclado, mouse ou joysticks. Ela também suporta
renderização 2d e 3d. É uma biblioteca bem completinha.

Aqui vai uma visão geral da arquitetura da Raylib. Ela espera que
você vá escrever seu jogo, engine ou ferramentas em cima dos módulos
da Raylib. Os módulos oferecem funcionalidades para controlar coisas
como câmera, texturas, texto, formas, modelos, áudio, matemática...

<figure style="text-align: center">
  <a href="/assets/images/posts/14-games-php/raylib-architecture.png" target="_blank">
    <img src="/assets/images/posts/14-games-php/raylib-architecture.png" alt="Visão geral da arquitetura da Raylib." />
  </a>
  <figcaption>Visão geral da arquitetura da Raylib. Fonte: https://www.raylib.com/index.html</figcaption>
</figure>

Ela não vem com coisas de engine, como detecção complexa de colisão ou
física. Se você precisar de algo desse tipo, precisará escrever por si.
Or encontrar algo já escrito por outra pessoa e que esteja preparado
para rodar com a Raylib.

## Extensão Raylib PHP

Recentemente uma extensão PHP chamou a minha atenção. Desenvolvida
por [@joseph-montanez](https://github.com/joseph-montanez) há um certo
tempo atrás, a extensão [**raylib-php**](https://github.com/joseph-montanez/raylib-php)
teve seu primeiro lançamento alpha pouco menos de um mês atrás.

**Se você precisa saber como compilar e rodar** por favor acesse o
arquivo README.md do repositório oficial. No MacOS os seguintes passos
funcionaram de boa pra mim:

```bash
$ git clone git@github.com:joseph-montanez/raylib-php.git
$ cd raylib-php/
$ phpize
$ ./configure
$ make
```

**Somente compilou tranquilo com o PHP 7.4 na minha máquina. Então
bota aí a versão correta do PHP.**

Essa extensão quer oferecer a mesma interface que a biblioteca em
C, então a gente poderá desenvolver os jogos mais ou menos da mesma
forma.

Claro que já que a biblioteca em C não traz coisas específicas de jogos
como física e outras paradinhas, você precisará implementar essas coisas
em PHP.

Esta extensão ainda não está completa. Você pode dar uma olhada no MAPPING.md
do repositório oficial pra entender o que já foi feito e o que falta.

Mesmo não estando completa, eu decidi brincar um pouco com a extensão e,
até onde consegui ver, já está bem funcional.

## Um jogo da cobrinha simplão

Mesmo sendo "Snake" (ou "jogo da cobrinha") um jogo bem simples eu
decidi não implementá-lo completamente. Meu principal objetivo aqui
era ter um jogo bom o suficiente pra eu poder testar algumas coisas
básicas da extensão.

Então eu resolvi pegar alguns requisitos pra implementar:

- A cobrinha precisa mover-se constantemente, mas pode mudar de direção
- Deverá existir apenas uma frutinha na tela, posicionada aleatóriamente
- Quando a cabeça da cobrinha toca numa fruta, cinco coisas devem acontecer: a fruta tem de ser destruída, o corpo da cobrinha deve crescer, outra fruta deve ser criada, o contador de pontos deve aumentar em 1 e a velocidade da cobrinha também deverá aumentar
- Quando a cobrinha toca na borda da tela, ela deverá aparecer do outro lado

Deveria ser claro, mas também é requisito que o jogador possa mudar a
direção em que a cobrinha anda usando alguma ferramenta de entrada como
o teclado.

Tem também dois requisitos bem importantes que eu decidi não implementar
aqui: 1) a cobrinha não pode morder a si mesma. Ou seja, se a cobrinha bater
em seu próprio corpo, o jogo deve acabar. 2) a cobrinha não pode mudar de
direção para um sentido diretamente oposto ao atual. Então quando se está
andando para a direita, mudar para a esquerda requer que primeiro se vá
para cima ou para baixo.

Estes dois requisitos não foram implementados pois se tratam de algorítmo
e não adicionariam muito para o experimento em si.

### Implementação

Essa implementação tem dois componentes: o Game Loop e o Game State.

O game loop é responsável por atualizar o estado do jogo baseado nas
entradas do(a) jogador(a) e cálculos e mais tarde por pintar este estado
na tela. Para isto eu criei uma classe chamada "_GameLoop_".

O game state mantém o estado atual do jogo (snapshot). Ele guarda
coisas como a pontuação do(a) jogador(a), as coordenadas x,y da
fruta, as coordenadas x,y da cobrinha e todos os quadradinhos que
formam o corpo da cobrinha. Para esta eu criei uma classe “_GameState_”.

Veja a seguir como estas classes são.

### Game Loop

A classe GameLoop inicializa o sistema, e cria um loop que executa
dois passos em cada iteração: atualizar o estado (update) e desenhar
o estado na tela (draw).

Então no construtor eu inicializei o canvas com largura e altura e
instanciei o GameState.

Como parâmetros ao GameState eu passei largura e altura divididos
por um tamanho de célula (30 pixels no meu caso). Estes valores
representam os valores máximos de coordenadas X e Y que o GameState
poderá trabalhar. A gente vai ver isso depois.

```php
// GameLoop.php
final class GameLoop
{
  // ...
  public function __construct(
    int $width,
    int $height
  ) {
    $this->width = $width;
    $this->height = $height;

  // 30
  $s = self::CELL_SIZE;
  $this->state = new GameState(
      (int) ($this->width / $s),
      (int) ($this->height / $s)
    );
  }
  // ...
}
```

Mais tarde, um método público chamado _start()_ vai criar uma Janela,
definir a taxa de frames e criar um loop infinito - sim, meio que um
`while (true)` - que vai primeiro chamar um método privado _update()_
e mais tarde um método _draw()_.

```php
// ...
public function start(): void
{
  Window::init(
    $this->width,
    $this->height,
    'PHP Snake'
  );
  Timming::setTargetFPS(60);

  while (
    $this->shouldStop ||
    !Window::shouldClose()
  ) {
    $this->update();
    $this->draw();
  }
}
// ...
```

O método _update()_ será responsável por atualizar a instância de
game state. Ele faz isso ao ler as entradas do(a) jogador(a)
(ao pressionar teclas) e fazendo coisas como verificar colisão e
por aí vai.

Baseado nos cálculos realizados no método _update()_, mudanças de
estado são enviadas à instância de _GameState_.

```php
private function update(): void
{
  $head = $this->state->snake[0];
  $recSnake = new Rectangle(
    (float) $head['x'],
    (float) $head['y'],
    1,
    1,
  );

  $fruit = $this->state->fruit;
  $recFruit = new Rectangle(
    (float) $fruit['x'],
    (float) $fruit['y'],
    1,
    1,
  );

  // Snake morde a fruta
  if (
    Collision::checkRecs(
      $recSnake,
      $recFruit
    )
  ) {
    $this->state->score();
  }

  // Controla velocidade do passo
  $now = microtime(true);
  if (
    $now - $this->lastStep
    > (1 / $this->state->score)
  ) {
    $this->state->step();
    $this->lastStep = $now;
  }

  // Atualiza a direção se necessário
  if (Key::isPressed(Key::W)) {
    $this->state->direction = GameState::DIRECTION_UP;
  } else if (Key::isPressed(Key::D)) {
    $this->state->direction = GameState::DIRECTION_RIGHT;
  } else if (Key::isPressed(Key::S)) {
    $this->state->direction = GameState::DIRECTION_DOWN;
  } else if (Key::isPressed(Key::A)) {
    $this->state->direction = GameState::DIRECTION_LEFT;
  }
}
```

Por último vem o método _draw()_. Ele vai ler as propriedades
do _GameState_ e pintá-las. Aplicando proporções e escalas.

Da forma como eu construí, este método espera que coordenadas X
variem de 0 até (largura dividida pelo tamanho da célula) e
coordenadas Y veriem de 0 até (altura dividida pelo tamanho da célula).
Ao multiplicar cada coordenada por "tamanho da célula" a gente consegue
desenhar com boas proporções sem precisar misturar o gerenciamento
de estado e desenho.

Bem simples. Fica assim:

```php
private function draw(): void
{
  Draw::begin();

  // Limpa a tela
  Draw::clearBackground(
    new Color(255, 255, 255, 255)
  );

  // Desenha a fruta
  $x = $this->state->fruit['x'];
  $y = $this->state->fruit['y'];
  Draw::rectangle(
    $x * self::CELL_SIZE,
    $y * self::CELL_SIZE,
    self::CELL_SIZE,
    self::CELL_SIZE,
    new Color(200, 110, 0, 255)
  );

  // Desenha o corpo da cobrinha
  foreach (
    $this->state->snake as $coords
  ) {
    $x = $coords['x'];
    $y = $coords['y'];
    Draw::rectangle(
      $x * self::CELL_SIZE,
      $y * self::CELL_SIZE,
      self::CELL_SIZE,
      self::CELL_SIZE,
      new Color(0,255, 0, 255)
    );
  }

  // Desenha a pontuação
  $score = "Score: {$this->state->score}";
  Text::draw(
    $score,
    $this->width - Text::measure($score, 12) - 10,
    10,
    12,
    new Color(0, 255, 0, 255)
  );

  Draw::end();
}
```

Tem algumas outras coisas que eu adicionei para depurar mas
eu prefiro deixá-las de fora deste artigo.

Depois disso, vem o gerenciamento de estado. Esta é a responsabilidade
de GameState. Vamo vê!

### Game State

O _GameState_ representa tudo que existe no game. Pontuação,
objetos como o(a) jogador(a) e as frutas.

Isto significa que sempre que o(a) jogador(a) precisar mover-se
ou uma fruta for comida, isto ocorrerá dentro de _GameState_.

Para o corpo da cobrinha eu decidi criar um array com coordenadas
(x,y) dentro. E eu considerei o primeiro elemento (índice zero)
como sendo a cabeça da cobrinha. Adicionar mais elementos (x,y)
neste array então deveria aumentar o tamanho do corpo da cobrinha.

Já a fruta é um simples par de coordenadas (x,y), pois eu espero
ter apenas uma fruta na tela por vez.

O construtor da classe _GameState_ inicializa estes objetos
com coordenadas aleatórias. Ficou assim: 

```php
// GameState.php
final class GameState
{
  public function __construct(
    int $maxX,
    int $maxY
  ) {
    $this->maxX = $maxX;
    $this->maxY = $maxY;

    $this->snake = [
      $this->craftRandomCoords(),
    ];

    $this->fruit = $this->craftRandomCoords();
  }
}
```

Para aumentar o tamanho do corpo da cobrinha, eu criei
um método privado chamado _incrementBody()_ que vai adicionar
uma nova cabeça ao corpo da cobrinha. Esta cabeça deverá
considerar a direção em que a cobrinha estava andando.
(esquerda, direita, acima ou abaixo)

Para criar uma nova cabeça, eu só copio a cabeça atual,
atualizo as coordenadas baseado na direção atual e mesclo
esta cópia com o corpo ocupando o índice zero.

```php
private function incrementBody(): void
{
  $newHead = $this->snake[0];

  // Ajusta a direção da cabeça
  switch ($this->direction) {
    case self::DIRECTION_UP:
      $newHead['y']--;
    break;
    case self::DIRECTION_DOWN:
      $newHead['y']++;
    break;
    case self::DIRECTION_RIGHT:
      $newHead['x']++;
    break;
    case self::DIRECTION_LEFT:
      $newHead['x']--;
    break;
  }

  // Adiciona nova cabeça,
  // na frente do corpo todo
  $this->snake = array_merge(
    [$newHead],
    $this->snake
  );
}
```

Tendo o método _incrementBody()_ fica bem fácil implementar
o método _score()_, que apenas aumenta a pontuação e o tamanho
do corpo da cobrinha. O _score()_ também vai criar uma nova
fruta numa coordenada aleatória da tela.

```php
public function score(): void
{
  $this->score++;
  $this->incrementBody();
  $this->fruit = $this->craftRandomCoords();
}
```

O mais interessante é o método _step()_, que é responsável
por mover a cobrinha.

Se você bem se lembrar, a forma como Snake se mexe é que a
cabeça vai constantemente andar em uma direção e o corpo a
segue. Então se Snake tem tamanho 3 e seu corpo está andando
para baixo, são necessários três passos para que ela ande
para a esquerda completamente.

A forma como eu fiz, foi basicamente aumentar o tamanho do
corpo novamente (que adiciona uma nova cabeça na nova direção)
e remover o último elemento do corpo da cobrinha. Desta forma
o tamanho corpo continua o mesmo e as coordenadas antigas
serão apagadas.

Eu também adicionei uma lógica para aparecer do outro lado
da tela quando a cabeça da cobrinha bater na borda da tela.

```php
public function step(): void
{
  $this->incrementBody();

  // Remove o último elemento
  array_pop($this->snake);

  // Move o corpo para o
  // outro lado da tela
  // se necessário
  foreach ($this->snake as &$coords) {
    if ($coords['x'] > $this->maxX - 1) {
      $coords['x'] = 0;
    } else if ($coords['x'] < 0) {
      $coords['x'] = $this->maxX - 1;
    }

    if ($coords['y'] > $this->maxY - 1) {
      $coords['y'] = 0;
    } else if ($coords['y'] < 0) {
      $coords['y'] = $this->maxY - 1;
    }
  }
}
```

Agora é só grudar tudo, instanciar as coisa e tamo pronto pra jogar!

## Faz sentido desenvolver jogos em PHP?

Certamente faz mais sentido que antes. Espero que menos que amanhã.

A extensão oferece interfaces bem bacanudas, mas ainda não está
completa. Se você sabe um pouco de C, você também pode tornar
o futuro um lugar melhor para desenvolvimento de jogos em PHP
ao contribuir com esta extensão.

[Aqui tem uma lista onde você pode encontrar funções que ainda precisam de implementação](https://github.com/joseph-montanez/raylib-php/blob/master/MAPPING.md).

O PHP ainda é bloqueante por padrão, então operações de E/S precisam
ser tratadas com cuidado. É possível utilizar esta biblioteca junto
de um Event Loop our usando threads da extensão Parallel. Provavelmente
você precisará escrever algo customizado pra isto.

O que mais me deixa encucado até o momento é sobre o quão portáveis
os jogos em PHP podem ser. Não tem uma forma simples de empacotar
estes jogos em binários. Então jogadores precisariam instalar o PHP
e compilar a extensão Raylib pra poder jogar algo.

Mas como eu mencionei, os primeiros passos foram dados. Então tecnicamente
já é mais fácil desenvolver jogos do que era antes.

Agradeço muito ao Joseph Montanez. Sua extensão me inspirou muito e
eu espero que esta publicação alcance e instigue mais desenvolvedores(as)
para ajudar no desenvolvimento dela. 

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
  "headline": "O jogo da cobrinha feito em PHP (com Raylib)",
  "description": "Eu vou te mostrar como é o código e as ferramentas que usei! Espero que isso tome sua atenção suficientemente para vermos esta extensão ganhar tração.",
  "image": [
    "{{ $page->getBaseUrl() }}/assets/images/posts/14-snake-640.webp"
   ],
  "datePublished": "2020-04-20T00:00:00+08:00",
  "dateModified": "2020-04-20T00:00:00+08:00",
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
