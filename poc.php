<?php
require_once __DIR__.'/vendor/autoload.php';

$tokens = token_get_all(file_get_contents('demo/app1.php'));

$halstead = new \Halstead\Halstead(new TokenType());


$N1 = $N2 = 0;
foreach($tokens as $data) {
    $token = new Token($data);
    $halstead->inventory($token);
}

$result = $halstead->calculate();
var_dump($result);