<?php
require_once __DIR__.'/vendor/autoload.php';


$files = glob('demo/*.php');

$loc = new \Loc\Loc();
$r = $loc->calculate('demo/app1.php');

var_dump($r);