<?php
require_once __DIR__ . '/../vendor/autoload.php';

$filename = 'demo/app1.php';

$path = isset($argv[1]) ? $argv[1] : false;

if(is_dir($path)) {
    $path = rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
    $directory = new RecursiveDirectoryIterator($path);
    $iterator = new RecursiveIteratorIterator($directory);
    $regex = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);
    $files = array();
    foreach($regex as $file) {
        $files[] = $file[0];
    }

} elseif(is_file($path)) {
    $files = array($path);
} else {
    die("PHP Metrics by Jean-François Lépine\nUsage: \n\tphp ".basename(__FILE__)." <directory or filename>\n");
}

foreach($files as $filename) {

    echo sprintf("\n%s:\n", $filename);

    echo "\tHalstead:\n";
    $halstead = new \Halstead\Halstead(new Token\TokenType());
    $rHalstead = $halstead->calculate($filename);

    echo ''
        . sprintf("\t\tVolume: %s\n", round($rHalstead->getVolume(), 2))
        . sprintf("\t\tLength: %s\n", $rHalstead->getLength())
        . sprintf("\t\tVocabulary: %s\n", $rHalstead->getVocabulary())
        . sprintf("\t\tEffort: %d\n", $rHalstead->getEffort())
        . sprintf("\t\tDifficulty: %s\n", round($rHalstead->getDifficulty(),2 ))
        . sprintf("\t\tDelivred Bugs: %s\n", round($rHalstead->getBugs(),2 ))
        . sprintf("\t\tTime: %s\n", round($rHalstead->getTime(),2 ))
    ;

    echo "\tLOC:\n";
    $loc = new \Loc\Loc();
    $rLoc = $loc->calculate($filename);
    echo ''
        . sprintf("\t\tLOC: %s\n", $rLoc->getLoc())
        . sprintf("\t\tLogical LOC: %s\n", $rLoc->getLogicalLoc())
        . sprintf("\t\tCyclomatic complexity: %s\n", $rLoc->getComplexityCyclomatic())
    ;

    echo "\tMaintenability:\n";
    $maintenability = new MaintenabilityIndex\MaintenabilityIndex;
    $rMaintenability = $maintenability->calculate($rHalstead, $rLoc);
    echo ''
        . sprintf("\t\tMaintenability Index: %s\n", round($rMaintenability->getMaintenabilityIndex(),2))
    ;

}



