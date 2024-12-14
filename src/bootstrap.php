<?php



$app = \Lack\Kindergarden\Cli\CliApplication::getInstance();
$app->node()->group("coder", "Coder commands");

$app->registerClass(\Lack\Kindergarden\Coder\Coder::class);


