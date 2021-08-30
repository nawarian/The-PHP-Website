<?php

declare(strict_types=1);

use PhpParser\Error;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;

require_once __DIR__ . '/vendor/autoload.php';

$parser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7);

$frameworks = new DirectoryIterator(__DIR__ . '/frameworks');

// Used for calculating the top of the pops
$mostUsedFunctions = [];
foreach ($frameworks as $framework) {
    if ($framework->isFile() || $framework->isDot()) {
        continue;
    }

    $path = new RecursiveDirectoryIterator($framework->getRealPath());
    $recursiveIterator = new RecursiveIteratorIterator($path);
    $frameworkFilesIterator = new RegexIterator($recursiveIterator, '#\.php$#', RegexIterator::MATCH);

    $internalFunctions = array_fill_keys(get_defined_functions()['internal'], 0);

    $traverser = new NodeTraverser();
    $traverser->addVisitor(new class($internalFunctions) extends NodeVisitorAbstract {
        private $internalFunctions = [];

        public function __construct(array &$internalFunctions)
        {
            $this->internalFunctions = &$internalFunctions;
        }

        public function enterNode(Node $node)
        {
            if (
                $node instanceof FuncCall
                && $node->name instanceof Node\Name
                && isset($this->internalFunctions[$node->name->toString()])
            ) {
                $this->internalFunctions[$node->name->toString()]++;
            }
        }
    });

    foreach ($frameworkFilesIterator as $file) {
        $realpath = str_replace('\\', '/', $file->getRealPath());
        if (
            false !== strpos($realpath, '/Tests/')
            || false !== strpos($realpath, '/tests/')
            || false !== strpos($realpath, '/vendor/')
            || false !== strpos($realpath, '/vendor-bin/')
        ) {
            continue;
        }

        $contents = file_get_contents($realpath);
        try {
            $ast = $parser->parse($contents);
            $traverser->traverse($ast);
        } catch (Error $error) {
            trigger_error(
                "Could not parse '{$realpath}'.",
                E_USER_WARNING
            );
            continue;
        }
    }

    arsort($internalFunctions);

    echo 'Framework: ' . $framework->getBasename() . PHP_EOL;
    foreach ($internalFunctions as $function => $callCount) {
        if ($callCount === 0) {
            continue;
        }

        $mostUsedFunctions[$function] = ($mostUsedFunctions[$function] ?? 0) + $callCount;
        echo "{$function} | {$callCount}" . PHP_EOL;
    }

    echo PHP_EOL;
}

arsort($mostUsedFunctions);
if (key($mostUsedFunctions) === 'sprintf') {
    // Remove sprintf from the top because Symfony abuses it
    array_shift($mostUsedFunctions);
}

$topOfThePops = array_slice($mostUsedFunctions, 0, 5);

echo 'Top 5' . PHP_EOL;
foreach ($topOfThePops as $function => $callCount) {
    echo "{$function} | {$callCount}" . PHP_EOL;
}

echo PHP_EOL;
