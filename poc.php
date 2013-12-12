<?php
require_once __DIR__.'/src/Token.php';
require_once __DIR__.'/src/TokenType.php';
require_once __DIR__.'/src/Halstead/Halstead.php';
require_once __DIR__.'/src/Halstead/Result.php';

$tokens =

$tokens = token_get_all(file_get_contents('demo/app1.php'));


$halstead = new Halstead\Halstead(new TokenType());


$N1 = $N2 = 0;
foreach($tokens as $data) {
    $token = new Token($data);
    $halstead->inventory($token);
}

$result = $halstead->calculate();
var_dump($result);