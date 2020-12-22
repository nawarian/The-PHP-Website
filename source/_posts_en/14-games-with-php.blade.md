---
slug: games-with-php
title: Simple PHP Game in PHP using Raylib: Snake (with source code)
category: guides
createdAt: 2020-04-12
sitemap:
  lastModified: 2020-04-12
image:
  url: /assets/images/posts/14-snake-640.webp
  alt: 'A colorful snake facing the camera.'
tags:
  - game
  - extension
  - curiosity
meta:
  description:
    I'm gonna show you how the code looks like and which tools I used!
    Hopefully it will catch your attention enough to see this extension
    getting traction.
  twitter:
    card: summary
    site: '@nawarian'
---

[Leia em Português](/br/edicao/jogos-em-php/)

Yes, you read it right!

A game. Written in PHP language.

Before I show you how, I'd like to to show you the results! Is not
polished, so lower your expectations for now. I just wanted a POC
good enough to be shown here 😬

You can see the gameplay on the video below.

<iframe src="https://player.vimeo.com/video/406784115" width="100%" height="400" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>

Cool, right!? And this is just a POC, but with the current state of
the extension you can already play around with different textures,
audios and so on.

I'm gonna show you how the code looks like and which tools I used!
Hopefully it will catch your attention enough to see this extension
getting traction.

Before anything, let me tell you a bit about raylib itself.

## Raylib

Written in C language, Raylib is defined as "a simple and
easy-to-use library to enjoy videogames programming".

It offers very straight forward functions to manipulate video, audio,
read inputs like keyboard, mouse or gamepads. It also supports 2d and
3d rendering. Is a pretty complete library.

Here's an overview of Raylib's architecture. It expects that you'll
write your game, engine or tools on top of Raylib's modules. Modules
offer features to handle things like camera, textures, text, shapes,
models, audio, math…

<figure style="text-align: center">
  <a href="/assets/images/posts/14-games-php/raylib-architecture.png" target="_blank">
    <img src="/assets/images/posts/14-games-php/raylib-architecture.png" alt="Raylib's architecture overview." />
  </a>
  <figcaption>Raylib's architecture overview. Source: https://www.raylib.com/index.html</figcaption>
</figure>

It doesn't come with engine stuff, like complicated collision detection
or physics. If you need such thing, you need to build it yourself. Or
find something already written by someone else that is ready to work
with Raylib.

## Raylib PHP Extension

Recently a PHP extension caught my attention. Developed by
[@joseph-montanez](https://github.com/joseph-montanez) quite a while
ago, the [**raylib-php**](https://github.com/joseph-montanez/raylib-php)
extension got its first alpha release less than a month ago.

**If you need to learn how to compile and run it** please head to the
repository's README.md file. On MacOS the following steps worked fine
for me:

```bash
$ git clone git@github.com:joseph-montanez/raylib-php.git
$ cd raylib-php/
$ phpize
$ ./configure
$ make
```

**It only compiled fine with PHP 7.4 for me. So make sure you have the
appropriate php version.**

This extension aims to provide bindings to the C library so we can
write same games in PHP.

Of course as the C library won't provide game-specific features like
physics and others. So such things would have to be developed on PHP side.

This extension is not complete yet. You can check the MAPPING.md file
from the official repository to understand what was already accomplished.

Even though it is not complete, I decided to play a bit with it and, as
far as I can see, it is already pretty functional.

## A Simple Snake Game

Even though "Snake" is a simple game I decided not to implement it
completely. My main goal here was to have a good enough running engine
that would test some basic features from the extension.

I picked then a couple of requirements to implement:

* The snake must be constantly moving, but may change direction
* There shall be one fruit placed in a random spot on the screen
* When the head of the snake hits a fruit five things must happen: the fruit must be destroyed, the snake's body must grow, another fruit must be created, the score counter must increase by one and Snake's speed also increases.
* When Snake hits a window edge it should warp to the opposite edge

Should be clear, but is also required that the player would change Snake's
direction using an input device, like a keyboard.

Two extremely important requirements I chose not to implement were 1)
that the Snake should not bite itself. Meaning that if by any reason
Snake hits its own body, the game should be over. And 2) that Snake
cannot change immediately to its opposite direction. So when you're heading
right, switching to left direction would require to first go up or down.

Those two requirements were not implemented as they as purely algorithmic
and wouldn't add much to the experimentation of the extension itself.

### Implementation

The implementation of this game has two components: a Game Loop and a
Game State.

The game loop is responsible for updating the game state based on user
inputs and calculations and later on painting this state on the screen.
For this I've created a class named "_GameLoop_".

The game state holds a snapshot of the game. It holds things like the
player's score, the fruit's x,y coordinates, the x,y coordinates from
Snake and all squares composing its body. For this one a “_GameState_”
class was created.

Here's how they look like.

### Game Loop

The GameLoop class initializes the system, and creates a loop that
executes two steps on each iteration: update state and draw state.

So in the constructor I just initialize the canvas width and height
and instantiate the GameState.

As parameters to the GameState I passed width and height divided by
a desired cell size (30 pixels in my case). Such values represent
the max X and Y coordinates the GameState can work with. We'll
check them later.

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

Later on, a public method named _start()_ will then spawn a Window,
set the frame rate and create an infinite loop - yes, a sort of
`while (true)` - that will first trigger a private method _update()_
and later on a method _draw()_.

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

The _update()_ method will be responsible for updating the game state
instance. It does this by reading user's input (key presses) and doing
things like checking collision and so on.

Based on calculations done in _update()_ method, it triggers state
changes on _GameState_ instance.

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

  // Snake bites fruit
  if (
    Collision::checkRecs(
      $recSnake,
      $recFruit
    )
  ) {
    $this->state->score();
  }

  // Controls step speed
  $now = microtime(true);
  if (
    $now - $this->lastStep
      > (1 / $this->state->score)
  ) {
    $this->state->step();
    $this->lastStep = $now;
  }

  // Update direction if necessary
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

Last comes the _draw()_ method. It will read properties on
_GameState_ and just print them. Applying all proportions and scales.

The way I've built this, it expects that X coordinates will range
from 0 to (width divided by cell size) and Y coordinates will range
from 0 to (height divided by cell size). By multiplying each coordinate
by "cell size" we get a good enough scaled drawing without mixing up
our state manipulation and drawing.

Quite simple. Looks like the following:

```php
private function draw(): void
{
  Draw::begin();

  // Clear screen
  Draw::clearBackground(
    new Color(255, 255, 255, 255)
  );

  // Draw fruit
  $x = $this->state->fruit['x'];
  $y = $this->state->fruit['y'];
  Draw::rectangle(
    $x * self::CELL_SIZE,
    $y * self::CELL_SIZE,
    self::CELL_SIZE,
    self::CELL_SIZE,
    new Color(200, 110, 0, 255)
  );

  // Draw snake's body
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

  // Draw score
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

There are some other things I've added for debugging but I'd rather
leave them out from this article.

After this, comes the state management. This is GameState's
responsibility. Check it out!

### Game State

The _GameState_ represents everything that exists in the game.
Scores, objects like the player and the fruits.

This means that whenever the player must move or a fruit must be
eaten, this will happen inside _GameState_.

For the Snake's body I decided to have an array with (x, y)
coordinates inside. And I consider the first element of the
array (index zero) to be the Snake's head. Adding more (x, y)
elements to this array would then increase the Snake's body size.

The fruit, though, is a single (x, y) coordinates pair. As I
expect to have only one fruit on screen each time.

The constructor of the _GameState_ class will initialize such
objects with random coordinates. It looks like this:

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

To increase the Snake's body size, I created a private method
named _incrementBody()_ which should add a new head to the
Snake's body. This new head shall consider the current direction
the Snake is heading to. (left, right, up or down)

To add a new head, I just create copy the current Head, update
its coordinates based on current direction and merge it to
Snake's body occupying the zero index.

```php
private function incrementBody(): void
{
  $newHead = $this->snake[0];

  // Adjusts head direction
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

  // Adds new head, in front
  // of the whole the body
  $this->snake = array_merge(
    [$newHead],
    $this->snake
  );
}
```

Having the _incrementBody()_ method in place makes it very
simple to implement the _score()_ method, which just increments
the score counter and the snake's body. Also _score()_ will
place a new fruit in a random coordinate.

```php
public function score(): void
{
  $this->score++;
  $this->incrementBody();
  $this->fruit = $this->craftRandomCoords();
}
```

The interesting one is the _step()_ method, which is responsible
for moving the snake.

If you remember well, the way Snake moves is that its head will
constantly step towards one direction and the body will then
follow it in a delayed fashion. So if the snake has 3 blocks
as body size and is moving downwards, it takes three steps to
make it face left completely.

The way I've done this was to basically increment the Snake's
body again (which adds a new head in the new direction) and
remove the last element from the snake's body. This way the
size remains the same, the head has the new direction and on
each step the old coordinates will be deleted.

I've also added some logic for warping from one edge of the
screen to another, you can read it (I hope).

```php
public function step(): void
{
  $this->incrementBody();

  // Remove last element
  array_pop($this->snake);

  // Warp body if necessary
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

Glue everything together and instantiate stuff. We're ready to play!

## Is it feasible to develop games in PHP?

Certainly it is more feasible than before. Hopefully less than tomorrow.

The extension provide really cool binding but is still not complete.
If you know a tiny bit of C code, you could make the future a better
place for game development in PHP by contributing to it.
[Here's a list where you can find the functions that still need implementation](https://github.com/joseph-montanez/raylib-php/blob/master/MAPPING.md).

PHP is still blocking by default, so heavy I/O should be smartly
handled. It is possible to use this library alongside an Event Loop
or using threads from the Parallel extension. Probably you'll have
to develop something yourself to achieve this.

What bugs me the most so far is how portable games written in php
can be. There's no simple way to package such games into binaries.
So players would have to install PHP and compile the Raylib extension
in order to play.

But as I mentioned, the first steps were already taken. So technically
speaking, it is already easier to achieve this than it was before.

Big thanks to Joseph Montanez. Your extension really inspired me and
I hope this post reaches and triggers more developers to support its
development.

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
  "headline": "Simple PHP Game in PHP using Raylib: Snake (with source code)",
  "description": "I'm gonna show you how the code looks like and which tools I used! Hopefully it will catch your attention enough to see this extension getting traction.",
  "image": [
    "{{ $page->getBaseUrl() }}/assets/images/posts/14-snake-640.webp"
   ],
  "datePublished": "2020-04-12T00:00:00+08:00",
  "dateModified": "2020-04-12T00:00:00+08:00",
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
