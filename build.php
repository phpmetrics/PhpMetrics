<?php
chdir(__DIR__);

if (!file_exists('vendor/autoload.php')) {
  echo '[ERROR] It\'s required to run "composer install" before building PhpMetrics!' . PHP_EOL;
  exit(1);
}

$filename = 'build/phpmetrics.phar';
if (file_exists($filename)) {
    unlink($filename);
}

$phar = new \Phar($filename, 0, 'phpmetrics.phar');
$phar->setSignatureAlgorithm(\Phar::SHA1);
$phar->startBuffering();


$files = array_merge(rglob('*.php'), rglob('*.twig'), rglob('*.json'), rglob('*.pp'));
$exclude = '!(.git)|(.svn)!';
foreach($files as $file) {
    if(preg_match($exclude, $file)) continue;
    $path = str_replace(__DIR__.'/', '', $file);
    $phar->addFromString($path, file_get_contents($file));
}

$phar->setStub(<<<STUB
#!/usr/bin/env php
<?php

/*
* This file is part of the PhpMetrics
*
* (c) Jean-François Lépine
*
* This source file is subject to the MIT license that is bundled
* with this source code in the file LICENSE.
*/

Phar::mapPhar('phpmetrics.phar');

require_once 'phar://phpmetrics.phar/vendor/autoload.php';
\$app = new Hal\Application\Console\PhpMetricsApplication('PhpMetrics, by Jean-François Lépine (https://twitter.com/Halleck45)', '0.0.7');
\$app->run();

__HALT_COMPILER();
STUB
);
$phar->stopBuffering();

chmod($filename, 0755);

function rglob($pattern='*', $flags = 0, $path='')
{
    $paths=glob($path.'*', GLOB_MARK|GLOB_ONLYDIR|GLOB_NOSORT);
    $files=glob($path.$pattern, $flags);
    foreach ($paths as $path) { $files=array_merge($files,rglob($pattern, $flags, $path)); }
    return $files;
}