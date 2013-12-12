<?php
require_once __DIR__.'/vendor/autoload.php';

$filename = 'demo/app1.php';

$tokens = token_get_all(file_get_contents($filename));
$halstead = new \Halstead\Halstead(new TokenType());

$N1 = $N2 = 0;
foreach($tokens as $data) {
    $token = new Token($data);
    $halstead->inventory($token);
}

$rHalstead = $halstead->calculate();



$loc = new \Loc\Loc();
$rLoc = $loc->calculate($filename);


$maintenability = new MaintenabilityIndex\MaintenabilityIndex;
$rMaintenability = $maintenability->calculate($rHalstead, $rLoc);



var_dump($rLoc);
var_dump($rHalstead);
var_dump($rMaintenability);
