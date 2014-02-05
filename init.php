<?php
function includeIfExists($file)
{
    if (file_exists($file)) {
        return include $file;
    }
}

if (
    (!$loader = includeIfExists('phar://metrics.phar/vendor/autoload.php'))
) {
    die(
        'You must set up the project dependencies, run the following commands:'.PHP_EOL.
        'curl -s http://getcomposer.org/installer | php'.PHP_EOL.
        'php composer.phar install'.PHP_EOL
    );
}

$app = new Hal\Console\PhpMetricsApplication('PhpMetrics, by Jean-François Lépine (https://twitter.com/Halleck45)', '0.0.3');
$app->run();