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
        if (
            false !== strpos($file->getRealPath(), '/Tests/')
            || false !== strpos($file->getRealPath(), '/tests/')
        ) {
            continue;
        }

        $contents = file_get_contents($file->getRealPath());
        try {
            $ast = $parser->parse($contents);
            $traverser->traverse($ast);
        } catch (Error $error) {
            trigger_error(
                "Could not parse '{$file->getRealPath()}'.",
                E_USER_WARNING
            );
            continue;
        }
    }

    asort($internalFunctions);

    echo 'Framework: ' . $framework->getBasename() . PHP_EOL;
    foreach ($internalFunctions as $function => $callCount) {
        if ($callCount === 0) {
            continue;
        }

        echo "{$function} | {$callCount}" . PHP_EOL;
    }

    echo PHP_EOL;
}
