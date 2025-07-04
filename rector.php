<?php

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withSets([
        \Rector\PHPUnit\Set\PHPUnitSetList::PHPUNIT_110,
    ])
    ->withRules([
        \Rector\TypeDeclaration\Rector\Class_\AddTestsVoidReturnTypeWhereNoReturnRector::class,
        \Rector\PHPUnit\CodeQuality\Rector\Class_\AddParentSetupCallOnSetupRector::class,
        \Rector\PHPUnit\PHPUnit60\Rector\ClassMethod\ExceptionAnnotationRector::class,
        \Rector\PHPUnit\PHPUnit100\Rector\StmtsAwareInterface\WithConsecutiveRector::class,
        \Rector\PHPUnit\PHPUnit100\Rector\Class_\StaticDataProviderClassMethodRector::class,
    ]);
