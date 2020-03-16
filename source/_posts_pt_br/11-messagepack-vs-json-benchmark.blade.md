---
slug: messagepack-vs-json-benchmark
lang: pt-br
title: Comparando JSON e MessagePack
category: walkthrough
createdAt: 2020-03-15
sitemap:
  lastModified: 2020-03-15
image:
  url: /assets/images/posts/11-messagepack-640.webp
  alt: 'Uma fotografia de vários chips de CPU.'
tags:
  - curiosidade
  - benchmark
  - serialização
  - json
meta:
  description:
    MessagePack ganha em praticamente todos os testes.
    Mas a diferença é tão pequena que eu quase não consigo
    ver benefícios em migrar do JSON para msgpack. Mas
    resultados mais interessantes podem brotar com a
    chegada do JIT no PHP 8...
  twitter:
    card: summary
    site: '@nawarian'
---

[Read in English](/en/issue/messagepack-vs-json-benchmark/)

## TL;DR

Sim, tecnicamente o MessagePack ganha em todos testes.
Mas a diferença é tão marginal que **eu não consigo ver
muitas vantagens em migrar do JSON para o MessagePack.**

Mas talvez faça sentido utilizar MessagePack desde o
início. **MessagePack é um tiquinho mais rápido e leve
se comparado ao JSON.**

**Utilizar MessagePack para trocar dados entre o navegador
e servidor não parece parece fazer muito sentido na minha
opinião.** O tamanho da resposta não faz muita diferença
quando se aplica o filtro gzip (alguns poucos bytes), mas
depurar as mensagens de rede passa a ser mais difícil.
Por outro lado, isto pode lhe ajudar a evitar que sua API
seja consumida por bots com tanta frequência.

[Você pode encontrar o código do benchmark e os números neste repositório que eu criei.](https://github.com/nawarian/msgpack-bm)

**Nota rápida:** Este artigo está HORRÍVEL em telas
pequenas. Desculpem-me por isso, mas eu preciso apresentar
tabelas para mostrar meus dados.

[Me dá um alô no twitter se você tiver uma forma melhor de representar esses dados em vez de tabular tudo.](https://twitter.com/nawarian)

## O que é MessagePack?

Como descrito no [site oficial do MessagePack](https://msgpack.org/)
ele é como JSON, mas rápido e leve.

Em outras palavras, **o MessagePack é um formato de
serialização que transforma estruturas de dados em
strings binárias.**

O motivo de ele ser tão eficiente é que as estruturas
de dados são mapeadas utilizando uma notação pequenininha
de binários em stream. O tamanho final é quase metade
do tamanho de algo serializado em JSON.

O exemplo do site oficial compara a mesma estrutura de
dados sendo representada nos formatos JSON e MessagePack.
O exemplo usado na home page mostra um map contendo duas
chaves: `compact = true` e `schema = 0`.

A versão **em JSON** deste map tem `27 bytes` enquanto
o **MessagePack** o faz em apenas `18 bytes`.

```php
// Map utilizado no
// exemplo oficial
$json = [
  "compact" => true,
  "schema" => 0,
];

// 27 bytes
json_encode($json);

// 18 bytes
msgpack_pack($json);
```

Eu aprendi sobre este formato muito recentemente e
por um completo acidente, enquanto lia este [tweet do @eminetto](https://twitter.com/eminetto/status/1237510796948758535)
, mas aparentemente isso existe desde 2012.

Me deixou encucado isso, já que eu trabalho com aplicações
de muito tráfego que constante mente troca mensagens em
JSON com diferentes serviços no back-end. Me cheira como
se fosse algo simples de implementar e ganhar rios de
performance.

**Eu então decidi fazer um benchmark do MessagePack no
PHP como extensão em C contra a extensão JSON nativa
do PHP (também escrita em C).**

## O ambiente de benchmark

Pra testar isso tudo, eu montei um
[repositório simplão com três benchmarks diferentes](https://github.com/nawarian/msgpack-bm).
Um arquivo testa a serialização msgpack, e
os outros dois testam a serialização JSON
sendo que o último desserializa utilizando
a opção "assoc" com valor true.

Pra executar estes benchmarks, eu escolhi utilizar
o Travis CI, já que qualquer pessoa pode reproduzir
estes testes de forma simplificada. Os dados que
consegui coletar do ambiente de execução são:

- CPU: Intel(R) Xeon(R); 1 @ 2,8 GHz; Cache 33 MB
- RAM: 7,79 GB
- OS: linux/amd64 (Ubuntu 16.04.6 LTS - Xenial)
- Versão do PHP: 7.4.3
- Versão do MsgPack: 2.1.0

Num futuro próximo eu vou atualizar o benchmark pra
rodar contra o PHP 8 e o Just In Time compiler dele.
[Como eu escrevi num outro post sobre como o JIT funciona](/br/edicao/php-8-jit/),
o JIT pode melhorar bastante a performance em oeprações
de CPU.

A entidade que eu utilizei pra serializar/desserializar
é uma resposta real da API de issues do github. Ela tem
2321 linhas e 147 KB de tamanho. Me parece um exemplo bem
decente pra representar dados reais de uma resposta de API.

**Você pode verificar a entidade aqui:**
[https://github.com/nawarian/msgpack-bm/blob/master/github-issues.json](https://github.com/nawarian/msgpack-bm/blob/master/github-issues.json).

## MessagePack é mais rápido e mais leve que o JSON

Como você pode notar, eu odeio esconder informação.
Te digo logo de cara: **O MessagePack ganha do JSON
em cada teste.**

Mas a diferença é bem pequetuxa, na real. Se liga:

### Tamanho das saídas:

Ao falar sobre APIs, uma das coisas mais importantes
é o tamanho do corpo da mensagem que está sendo
transportada na rede. Os valores crus são bem
impressionantes, but todo bom programador(a) sabe que
na maioria dos casos **devemos comprimir nossas APIs
usando filtros como gzip ou brotli.**

Então para esta comparação eu decidi mostrar o tamanho
do conteúdo serializado nos dois formatos e adicionei
também a versão comprimida com gzip.

A comparação ficou assim:

Formato | Serializado (bytes) | Serializado + Gzip (bytes)
------ | --------------- | -------------------------
JSON | 143025 | 26214
MessagePack | 120799 (-22226) | 26074 (-140)

Como pode-se notar, quando não há filtros de compressão
o MessagePack é cerca de 22 KB mais leve que o JSON. Mas
quando aplicamos o gzip nos dois valores, MessagePack
passa a ganhar por míseros 140 bytes. **Nada expressivo.**

### Tempos de Serialização/Desserialização:

A outra parte importante no processo de serialização
é quanto tempo leva pra transformar aquele formato.
Para tal eu decidi **serializar e desserializar a
mesma entidade várias vezes** e tomar notas sobre
o consumo de memória e **tempos de processamento.**

O consumo de memória não parece mudar muito neste
teste a não ser que você passe a desserializar a
mesma entidade um milhão de vezes, o que eu espero
não ser tão comum para a maioria das aplicações PHP.
Portanto **eu não vou apresentar os números sobre
utilização de memória já que a variação foi de 0 bytes.**

Enquanto eu coletava os números sobre JSON, **eu
descobri que desserializar uma entidade com assoc = true
é um pouquinho mais rápido em comparação com assoc = false.**
O que é um tanto interessante e até faz um certo
sentido.

Já que os resultados com assoc = true são melhores
para o JSON, eu vou utilizar estes dados somente na
comparação.

O resultado:

Iterações | Serialização JSON (s) | Serialização MessagePack (s) | Desserialização JSON (s) |  Desserialização MessagePack (s)
----- | ----------------- | ------------------------ | ----------------- | ------------------------
1 | 0.00064 | 0.00019 (-0,00045) | 0.00164 | 0.00051 (-0,00113)
10 | 0.00340 | 0.00082 (-0,00258) | 0.00866 | 0.00194 (-0,00672)
100 | 0.03135 | 0.00732 (-0,02403) | 0.07905 | 0.01700 (-0,06205)
1000 | 0.30385 | 0.07250 (-0,23135) | 0.77422 | 0.16785 (-0,60637)
10000 | 3.02723 | 0.72503 (-2,95472) | 7.74523 | 1.65804 (-6,08719)
100000 | 30.29353 | 7.25324 (-23,04029) | 77.48423 | 16.71792 (-60,76631)

As **iterações** aqui significam quantas vezes nós
executamos a mesma operação. Sendo a operação um
`json_encode`, `msgpack_pack`, `json_decode` ou
`msgpack_unpack`.

Pessoalmente eu prestaria atenção nos números de 1 a 100
iterações. Acima deste número, começa a ficar menos realista
para mim. Eu os deixei alí de toda forma, os resultados
começam a ficar bem interessantes a partir de 10 mil
iterações.

Como você pode perceber, as diferenças são bem pequenas
nas primeiras iterações.

Quando uma única operação de serialização é chamada,
**MessagePack** é 0,45 ms mais rápido. **Nada expressivo.**
Quando o número de serializações passa a 100, a diferença
começa a se tornar evidente e o **MessagePack é 24 ms
mais rápido que o JSON.**

Desserializações são normalmente mais lentas para
os dois formatos, mas o MessagePack ganha aqui
novamente. Quando uma única desserialização ocorre,
**MessagePack é 1 ms mais rápido.** Enquanto 100
desserializações são **62 ms mais rápidas com
MessagePack em comparação com JSON.**

Mesmo que a diferença seja grande o suficiente
quando 100 items precisam ser desserializados,
eu acredito que na maioria das aplicações PHP seja
bem improvável de acontecer. Um número de operações
entre 1 e 10 é bem plausível pra mim e **o MessagePack
é 2 ms mais rápido em serializações e 6 ms em
desserializações quando executado 10 vezes.**

Bons números, mas nada muito expressivo.

## Devo migrar do JSON para o MessagePack?

Quando se trata de engenharia de software, a única
resposta que podemos dar com certeza é: **depende.**
Toda aplicação tem diferentes desafios e situações.

Por exemplo, se você estiver trocando arquivos
entre diferentes sistemas e comprimir o seu conteúdo
não é uma opção, então o MessagePack pode ser ótimo
pra economizar espaçø em disco e reduzir a carga
numa operação de stream.

Uma aplicação comunicando com microsserviços no
back-end pode se beneficiar da velocidade que o
MessagePack traz se o número de interações for
maior que 10 por requisição.

Eu suspeito (apesar de não ter testado) que
serializar/desserializar o MessagePack no
JavaScript seja um tanto mais lento se comparado
ao json, já que o MessagePack não roda como parte
do motor JavaScript (Node, V8). Então possívelmente
aplicações Front-End não se beneficiariam tanto
do MessagePack ainda.

Além disso, depurar respostas na aba Network do navegador
se tornaria insuportável. Por outro lado, isto pode lhe
ajudar a obfuscar sua API e evitar crawlers espertinhos
já que o MessagePack ainda não é tão conhecido.

Assim como qualquer outro benchmark, este aqui é
bem inútil se você estiver buscando uma resposta
de fácil utilização. Você precisará adaptar isso aqui
para o seu cenário e ver como o MessagePack se comporta.

Felizmente migrar de um formato para o outro deveria
ser tão simples quanto trocar uma chamada de `json_encode`
para `msgpack_pack` e de `json_decode` para `msgpack_unpack`.
No caso de comunicar-se com microsserviços, um simples
cabeçalho `Accept` já deve lhe resolver a vida.

Claro que quanto mais for necessário refatorar, maior
o custo de implementar e testar essas mudanças. Portanto
tenha certeza de que você analisou os possíveis ganhos
antes de tentar mudar todos seus serviços e consumidores
para este novo formato.

Pra mim um trabalho de 30 minutos para 2 ms de performance
parece ser justo. Mas gastar 3 semanas para os mesmos
2 ms não parece fazer muito sentido. Ao menos não na
escala em que estou acostumado a trabalhar.

**Utilizar o MessagePack desde o começo parece fazer
muito sentido.** Já que ele ganha do JSON em todos os
testes. Então se você for escrever algo novo, considere o
MessagePack.

---

Não se esqueça de compartilhar isso com seus(uas) amigos(as)
e colegas nerdões(onas). Eu tenho certeza de que o MessagePack
será uma boa opção para muitos(as) deles(as).

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
  "headline": "Comparando JSON e MessagePack",
  "description": "MessagePack ganha em praticamente todos os testes. Mas a diferença é tão pequena que eu quase não consigo ver benefícios em migrar do JSON para msgpack. Mas resultados mais interessantes podem brotar com a chegada do JIT no PHP 8...",
  "image": [
    "{{ $page->getBaseUrl() }}/assets/images/posts/11-messagepack-640.webp"
   ],
  "datePublished": "2020-03-15T00:00:00+08:00",
  "dateModified": "2020-03-15T00:00:00+08:00",
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
