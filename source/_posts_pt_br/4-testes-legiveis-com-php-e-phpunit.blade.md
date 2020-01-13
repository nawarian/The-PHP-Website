---
slug: testes-legiveis-com-php-e-phpunit
lang: pt-br
title: Testes legíveis com PHP e PHPUnit
createdAt: 2020-01-07
sitemap:
  lastModified: 2020-01-07
image:
  url: /assets/images/4-writing-great-tests.jpg
  alt: 'Vários(as) desenvolvedores(as) olhando para a mesma tela de computador tentando entender o que se passa'
meta:
  description:
    Este post tem a intenção de lhe ajudar a reduzir o número de "Diabéiss"
    por segundo enquanto escreve, lê e muda código de teste em sua aplicação
    PHP usando o framework de testes PHPUnit.
  twitter:
    card: summary
    site: '@nawarian'
---

[Read in english](/en/issue/clean-tests-with-php-and-phpunit/)

Existem várias ferramentas disponíveis no ecossistema PHP que estão preparadas
para oferecer uma ótima experiência com testes.
[PHPUnit é de longe a mais famosa de todas](https://github.com/sebastianbergmann/phpunit)
. É quase um sinônimo de teste nessa linguagem.

As boas práticas não são bem compartilhadas na comunidade. Existem tantas
opções de quando e por quê escrever testes, quais tipos de testes e por
aí vai. Mas na verdade **não faz sentido algum escrever qualquer teste
se você não for capaz de lê-los mais tarde**.

## Testes são uma forma muito especial de documentação

Como eu já mencionei no [post sobre TDD com PHP](/br/edicao/tdd-com-php-na-vida-real/),
um teste sempre vai (ou pelo menos deveria) tornar claro o que um certo
pedaço de código deve atingir como objetivo.

**Se um teste não consegue expressar uma ideia, é um teste ruim.**

Com isso em mente, eu preparei uma lista com boas práticas que podem
auxiliar desenvolvedores(as) php a escrever testes bons, legíveis e
úteis.

## Começando pelo básico

Existem algumas práticas que muitas pessoas seguem sem sequer questionar o
motivo. Eu vou listar algumas delas e tentar explicar pelo menos por cima
qual a razão de tais práticas.

### Testes não deveriam fazer operações E/S

**Motivo**: E/S é lento e instável.

**Lento:** mesmo com o melhor equipamento na face da terra, E/S ainda será
mais lento que acesso a memória. **Testes devem sempre rodar rápido**, do
contrário ninguém irá rodá-los suficientemente.

**Instável:** um certo arquivo, binário, socket, pasta ou entrada DNS pode
não estar disponível em todas as máquinas em que seu código será executado.
**Quanto mais você depende de E/S em seus testes, mais seus testes ficam
amarrados e dependentes de infraestrutura**. 

Operações consideradas E/S:
- Ler/escrever arquivos
- Chamadas de rede
- Chamadas a processos externos (usando exec, proc_open...)

Existem casos onde ter E/S fará com que o teste seja escrito mais rapidamente.
**Mas se liga**: fazer com que essas operações funcionem da mesma forma nos
ambientes de desenvolvimento, build e deployment pode se tornar uma imensa
dor de cabeça.

**Isolando testes para que não precisam de E/S:** abaixo eu mostro
uma decisão de design que pode ser tomada para evitar que seus testes
realizem operações de E/S **segregando responsabilidades para interfaces**.

Segue o exemplo:

```php
public function getPeople(): array
{
  $rawPeople = file_get_contents(
    'people.json'
  ) ?? '[]';

  return json_decode(
    $rawPeople,
    true
  );
}
```

No momento em que começarmos a testar este método, seremos forçados a
criar um arquivo local pra teste e, de tempos em tempos, manter uma
snapshot desse arquivo. Como no seguinte:

```php
public function testGetPeopleReturnsPeopleList(): void
{
  $people = $this->peopleService
    ->getPeople();

  // assert it contains people
}
```

Pra esse tipo, é necessário **definir pré condições** para que ele possa passar.
Mesmo que pareça fazer sentido a primeira vista, isto é na realidade **terrível**.

**Pular um teste por conta de uma pré condição faltante não garante qualidade de
software. Apenas esconde bugs!**

**Corrigindo:** basta isolar as operações de E/S ao mover a responsabilidade para
uma interface.

```php
// extrai a lógica
// de buscar algo
// p/ uma interface
// especializada
interface PeopleProvider
{
  public function getPeople(): array;
}

// implementação concreta
class JsonFilePeopleProvider
  implements PeopleProvider
{
  private const PEOPLE_JSON =
    'people.json';

  public function getPeople(): array
  {
    $rawPeople = file_get_contents(
      self::PEOPLE_JSON
    ) ?? '[]';

    return json_decode(
      $rawPeople,
      true
    );
  }
}

class PeopleService
{
  // injetar via __construct()
  private PeopleProvider $peopleProvider;

  public function getPeople(): array
  {
    return $this->peopleProvider
      ->getPeople();
  }
}
```

Tô sabendo, agora `JsonFilePeopleProvider` usa E/S de toda forma. Verdade.

Em vez de `file_get_contents()` a gente pode usar um layer de abstração
como o [Filesystem do Flysystem](https://flysystem.thephpleague.com/docs/adapter/local/)
que pode ser facilmente mockado.

E pra quê serve o `PeopleService` então? Boa pergunta... Isto é uma das coisas
que **testes nos traz: questionar o nosso design, remover código inutil.**

---
### Testes devem ser concisos e ter significado

**Motivo:** testes são uma forma de documentação. Mantenha-os limpos,
curtos e legíveis.

**Limpos e curtos**: sem bagunça, sem escrever mil linhas de mock,
sem escrever trocentos asserts no mesmo teste.

**Legíveis:** cada teste deve contar uma história. A estrutura "Given, When, Then"
é perfeita pra isso.

Aqui vão algumas características de um teste bem escrito:
- Contém apenas asserts necessários (preferivelmente apenas um)
- Lhe conta exatamente o que deveria acontecer dada certa condição
- Testa apenas um caminho de execução do método por vez
- Não mocka o universo inteiro para fazer algum assert

**Importante notar** que se a sua implementação possui alguns IFs, switch ou
iterações, **todos estes caminhos alternativos devem ser explicitamente testados.**
Então early returns, por exemplo, devem sempre conter testes.

Novamente: **não importa o coverage, o que importa é documentar.**

Deixa eu te mostrar como um teste confuso se parece:

```php
public function testCanFly(): void
{
  $semAsas = new Person(0);
  $this->assertEquals(
    false,
    $semAsas->canFly()
  );

  $umaAsa = new Person(1);
  $this->assertTrue(
    !$$umaAsa->canFly()
  );

  $duasAsas = new Person(2);
  $this->assertTrue(
    $$duasAsas->canFly()
  );
}
```

Vamos então adotar o "Given, When, Then" e ver como esse teste muda:

```php
public function testCanFly(): void
{
  // Given
  $person = $this->givenAPersonHasNoWings();

  // Then
  $this->assertEquals(
    false,
    $person->canFly()
  );

  // Outros casos...
}

private function givenAPersonHasNoWings(): Person
{
  return new Person(0);
}
```

Assim como as cláusulas "Given", os "When"s e "Then"s também podem
ser extraídos para métodos privados. Qualquer coisa que faça seu
teste ficar mais legível.

Agora, aquele assertEquals tá bem bagunçado e com pouquíssimo significado.
Um humano lendo isso precisa interpretar a assertion pra entender o que
deveria significar.

**Usar assertions específicas tornam seus testes muito mais legíveis.**
`assertTrue()` deveria receber uma variável contendo um booleano, nunca
uma expressão como `canFly() !== true`.

Então do exemplo anterior, vamos substituir o `assertEquals` entre `false`
e `$person->canFly()` com um simples `assertFalse`:

```php
// ...
$person = $this->givenAPersonHasNoWings();

$this->assertFalse(
  $person->canFly()
);

// Outros casos...
```

Limpinho! Dado que uma pessoa não tem asas, ela não deveria poder voar!
Dá pra ler como se fosse um poema 😍

Agora, esse "Outros casos" aparecendo duas vezes no nosso texto já é uma
boa pista de que este teste está fazendo muitas assertions. Ao mesmo tempo
o nome do método `testCanFly()` não significa nada muito útil.

Vamos tornar o nosso test case um tequinho melhor:

```php
public function testCanFlyIsFalsyWhenPersonHasNoWings(): void
{
  $person = $this->givenAPersonHasNoWings();
  $this->assertFalse(
    $person->canFly()
  );
}

public function testCanFlyIsTruthyWhenPersonHasTwoWings(): void
{
  $person = $this->givenAPersonHasTwoWings();
  $this->assertTrue(
    $person->canFly()
  );
}

// ...
```

A gente poderia inclusive renomear o teste pra bater com um cenário da
vida real como `testPersonCantFlyWithoutWings`, mas pra mim o nome já
parece bom o suficiente.

---
### Um teste não deve depender de outro

**Motivo:** um teste deveria ser capaz de rodar e passar em qualquer ordem.

Até o presente momento, eu não consigo encontrar um bom motivo para acoplar
testes.

Recentemente eu fui perguntado sobre como testar uma feature para usuários
logados e eu gostaria de utilizar isto como exemplo aqui.

O teste faria o seguinte:
- Gerar um token JWT
- Executar uma certa tarefa logado
- Fazer os asserts

A forma como o teste foi feito era a seguinte:

```php
public function testGenerateJWTToken(): void
{
  // ... $token
  $this->token = $token;
}

// @depends testGenerateJWTToken
public function testExecuteAnAmazingFeature(): void
{
  // Execute using $this->token
}

// @depends testExecuteAnAmazingFeature
public function testStateIsBlah(): void
{
  // Busca os resultados
  // interface logada
}
```

Este teste é ruim por alguns motivos:
- PHPUnit não garantirá a ordem de execução dos testes
- Os testes não podem ser executados de forma independente
- Testes rodando em paralelo irão falhar aleatoriamente

A forma mais simples de resolver este problema que eu consigo pensar,
novamente, é com "Given, When, Then". Desta forma a gente torna os
testes mais concisos e conta uma história ao mostrar suas dependências
de forma clara que explica a feature em si.

```php
public function testAmazingFeatureChangesState(): void
{
  // Given
  $token = $this->givenImAuthenticated();
  
  // When
  $this->whenIExecuteMyAmazingFeature(
    $token
  );
  $newState = $this->pollStateFromInterface(
    $token
  );

  // Then
  $this->assertEquals(
    'my-state',
    $newState
  );
}
```

A gente precisaria também escrever testes para autenticar e por aí vai.
Esta estrutura é tão massa que o
[Behat a utiliza por padrão](https://behat.org/en/latest/quick_start.html).

---
### Sempre injete as dependências

**Motivo:** mockar estado global é terrível, não ser capaz de mockar as
dependências torna impossível testar uma funcionalidade.

Aqui vai uma lição para a vida: **Esqueça sobre classes estáticas que mantém
estado e também instâncias singleton.** Se a sua classe depende de algo,
torne-o injetável.

Aqui vai um exemplo particularmente triste:
```php
class FeatureToggle
{
  public function isActive(
    Id $feature
  ): bool {
    $cookieName = $feature->getCookieName();

    // Early return se o
    // cookie está presente
    if (Cookies::exists(
      $cookieName
    )) {
      return Cookies::get(
        $cookieName
      );
    }

    // Calcular feature toggle...
  }
}
```

Agora. Como você poderia testar este early return?

Não consegue né, Moisés?

Pra testar este método, nós precisaríamos entender o comportamento
desta classe `Cookies` e tomar certeza de que conseguiríamos reproduzir
todo o ambiente por trás desta classe para forçar alguns retornos.

Faz isso não.

A gente pode consertar essa situação injetando uma instância de `Cookies`
como dependência. O teste ficaria parecido com o seguinte:

```php
// Test class...
private Cookies $cookieMock;

private FeatureToggle $service;

// Preparing our service and dependencies
public function setUp(): void
{
  $this->cookieMock = $this->prophesize(
    Cookies::class
  );

  $this->service = new FeatureToggle(
    $this->cookieMock->reveal()
  );
}

public function testIsActiveIsOverriddenByCookies(): void
{
  // Given
  $feature = $this->givenFeatureXExists();

  // When
  $this->whenCookieOverridesFeatureWithTrue(
    $feature
  );

  // Then
  $this->assertTrue(
    $this->service->isActive($feature)
  );
  // poderíamos também testar que
  // nenhum outro método foi chamado
}

private function givenFeatureXExists(): Id
{
  // ...
  return $feature;
}

private function whenCookieOverridesFeatureWithTrue(
  Id $feature
): void {
  $cookieName = $feature->getCookieName();
  $this->cookieMock->exists($cookieName)
    ->shouldBeCalledOnce()
    ->willReturn(true);

  $this->cookieMock->get($cookieName)
    ->shouldBeCalledOnce()
    ->willReturn(true);
}
```

**O mesmo acontece com singletons.** Então se você quer **tornar um objeto
único**, é só **configurar o seu injetor de dependências direito** em vez
de utilizar o Singleton (anti) pattern.

Do contrário você vai acabar escrevendo métodos como `reset()` ou
`setInstance()`, que só são úteis para classes de teste. Me soa no
mínimo estranho. 

Mudar o seu desgin para tornar testes mais simples está tudo bem. **Criar
métodos para tornar testes mais simples não está ok.**

---
### Nunca teste métodos protected/private

**Motivo:** a forma como testamos uma funcionalidade é fazendo assertions
em como a sua assinatura se comporta: dada uma condição, quando eu faço X,
espero que Y aconteça.
**Métodos protected/private não são parte da assinatura de uma funcionalidade.**

Eu vou inclusive me recusar a te mostrar uma forma de "testar" métodos privados,
mas aqui vai uma dica: você fazer isso com a [reflection API](https://www.php.net/manual/en/book.reflection.php).

Por favor, castigue-se de alguma forma sempre que pensar em utilizar reflections
pra testar um método privado!

Por definição, métodos privados vão somente ser chamados de dentro da classe.
Então não são publicamente acessíveis. Isto significa que apenas métodos
públicos nesta mesma classe consegue invocar tais métodos privados.

**Se você testou todos os métodos públicos, você também deve ter testado os
protegidos/privados de uma vez só.** Se não, pode apagar cada um deles, que
ninguém tá usando eles mesmo.

---
## Além do básico: as coisas interessantes

Espero que você não tenha ficado entediado(a) até aqui. Básico é básico, mas
precisa ser escrito.

Agora, durante as próximas linhas, vou compartilhar contigo algumas opiniões
que carrego sobre testes limpos e cada decisão que impacta meu fluxo de
desenvolvimento.

**Eu diria que os valores mais importantes que levo em consideração enquanto
escrevo testes são os seguintes:**
- Aprendizado
- Receber feedback rápido
- Documentação
- Refatoração
- Design enquanto testo

Cada opinião que exponho abaixo segue ao menos um destes valores e cada
uma dá suporte à outra.

### Teste vem primeiro, não depois

**Valores**: aprendizado, receber feedback rápido, documentação, refatoração,
design enquanto testo.

Esta é a base de tudo. É tão importante que carrega todos os valores de uma
vez só.

Escrever teste primeiro lhe força entender como o seu "given, when, then"
deve ser estruturado. **Você documenta primeiro ao escrever assim** e,
mais importante ainda, **aprende e torna explícito seus requisitos** como
coisas mais relevantes no software.

**Te parece estranho escrever um teste antes de escrever algo?** Imagine
o quão embaraçoso é implementar algo e, enquanto testa, descobrir que todos
os "given, when, then" não têm sentido algum.

Testar primeiro também te permite rodar os testes contra as expectativas a
cada 2 segundos. **Você recebe feedback de suas mudanças da forma mais rápida
possível. Não importa o quão grande ou pequena a feature possa parecer.**

**Testes que estão passando indicam as melhores áreas para refatorar no sistema.**
Em algum momento eu provavelmente escreverei sobre refatoração, mas a coisa é:
sem teste, sem refatoração. Porque refatorar sem testes é simplesmente arriscado
demais.

E por último, mas não menos importante, ao definir o seu "given, when, then"
fica claro quais interfaces seus métodos devem ter e como elas devem se
comportar. **Manter este teste limpo também irá lhe forçar a tomar diferentes
decisões de design.**

Irá lhe forçar a criar factories, interfaces, quebrar heranças e por aí vai.
E, sim, para tornar o teste mais simples!

Se seus testes são um documento vivo que pretende explicar como o software
funciona, **é extremamente importante que eles expliquem de forma clara.**

---
### Não ter testes é melhor que ter testes mal feitos

**Valores**: aprendizado, documentação, refatoração.

Muitos(as) desenvolvedores(as) escrevem teste da seguinte forma: escreve
a funcionalidade, soca o framework de teste até cobrir um certo tanto de
linhas e enviam pra produção.

O que eu gostaria que fosse levado em consideração com mais frequência, porém,
é quando o(a) próximo(a) desenvolvedor(a) visita esta funcionalidade. **O que
os testes estão realmente contando a esta pessoa...**

Normalmente testes cujo nome não dizem muita coisa, são testes mal escritos.
O que é mais claro pra ti: `testCanFly` ou
`testCanFlyReturnsFalseWhenPersonHasNoWings`?

Sempre que seu teste não representar nada além de bagunça e códigos forçando
ao framework aumentar o coverage com exemplos que não parecem fazer qualquer
sentido, é hora de parar e pensar se vale a pena escrever este teste.

Até mesmo coisas bestas como nomear uma variável como `$a` e `$b`, ou dar nomes
que não se relacionam com o caso de uso.

**Lembre-se:** testes são um documento vivo, tentando explicar como o software
deveria se comportar. `assertFalse($a->canFly())` não documenta muita coisa.
Já `assertFalse($personWithNoWingos->canFly())` documenta.

---
### Rode seus testes compulsivamente

**Valores**: aprendizado, receber feedback rápido, refatoração.

**Antes de iniciar qualquer funcionalidade: rode os testes.** Se os testes
estiverem quebrados antes de você tocar qualquer coisa, você saberá _antes_
de escrever qualquer código e não irá gastar preciosos minutos depurando
testes quebrados que você nem tinha conhecimento sobre.

**Após salvar um arquivo: rode os testes.** O quanto antes você souber que
quebrou algo, mais cedo saberá como corrigir o problema e continuar. Se
interromper seu fluxo de trabalho para corrigir um problema te parece
improdutivo, imagina só voltar vários passos atrás pra corrigir um problema
que você não fazia ideia ter causado.

**Depois de trocar uma ideia com o(a) colega ou verificar suas notificações
do github: rode os testes.** Se o teste estiver vermelho, você sabe onde
parou. Se estiverem verdes, você sabe que pode continuar.

**Antes de refatorar algo, até mesmo nomes de variáveis: rode os testes.**

Sério mesmo, rode os testes. É de graça. Rode os testes com a mesma
frequência em que salva seus arquivos.

Na real, o [PHPUnit Watcher](https://github.com/spatie/phpunit-watcher)
resolve exatamente esse problema pra gente e até envia notificação
quando os testes rodam.

---
### Grandes testes, grandes responsabilidades

**Valores**: aprendizado, refatoração, design enquanto testo.

Seria ideal que cada classe teria ao menos um caso de teste pra si.
E também que cada método público fosse coberto com testes. E cada
fluxo alternativo (if/switch/try-catch/exception)...

Contemos mais ou menos assim:

- 1 classe = 1 caso de teste
- 1 método = 1 ou mais testes
- 1 fluxo alternativo (if/switch/try-catch/exception) = 1 teste

Então um código simples como o abaixo deveria ter 4 testes diferentes:

```php
// class Person
public function eatSlice(Pizza $pizza): void
{
  // testar exception
  if ([] === $pizza->slices()) {
    throw new LogicException('...');
  }
  
  // testar exception
  if (true === $this->isFull()) {
    throw new LogicException('...');
  }

  // testar caminho padrão (slices = 1)
  $slices = 1;
  // testar caminho alternativo (slices = 2)
  if (true === $this->isVeryHungry()) {
    $slices = 2;
  }

  $pizza->removeSlices($slices);
}
```

**Quanto mais métodos públicos, mais testes.**

E ninguém gosta de ler documentos longos. Como seu caso de teste também é
um documento, deixá-lo pequeno e conciso irá aumentar a sua qualidade e
utilidade.

Isto também é um grande sinal de que sua classe está acumulando
responsabilidades e pode ser hora de botar o chapéu de refatoração pra
remover funcionalidades, mover para classes diferentes ou repensar parte
do seu design.

---
### Mantenha uma suite de regressão

**Valores**: aprendizado, documentação, receber feedback rápido. 

Se liga nessa função:

```php
function findById(string $id): object
{
  return fromDb((int) $id);
}
```

Você esperou alguém passar `"10"` mas, em vez disso, foi passado `"10 bananas"`.
Ambas formas acham o valor, mas uma não deveria. Você tem um bug.

A primeira coisa a fazer? Escrever um teste que descreva que este comportamento
está errado!!

```php
public function testFindByIdAcceptsOnlyNumericIds(): void
{
  $this->expectException(InvalidArgumentException::class);
  $this->expectExceptionMessage(
    'Only numeric IDs are allowed.'
  );

  findById("10 bananas");
}
```

Testes não estão passando, é claro. Mas agora você sabe o que fazer para
fazê-los passar. Remova o bug, faça o teste passar, joga no master,
dá aquele deploy e vai ser feliz!

Mantenha este teste ali, pra sempre. Se possível, numa suite de testes
especializada em regressão e conecte este teste com uma issue.

E prontinho! Feedback rápido enquanto corrige bugs, documentação feita,
código a prova de regressões e felicidade.
