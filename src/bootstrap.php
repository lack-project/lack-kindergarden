<?php



$app = \Lack\Kindergarden\Cli\CliApplication::getInstance();
$app->node()->group("coder", "Coder commands");

$app->registerClass(\Lack\Kindergarden\Coder\Coder::class);
$app->registerClass(\Lack\Kindergarden\Coder\BL\CoderPrepare::class);
$app->registerClass(\Lack\Kindergarden\Coder\BL\CoderRun::class);


\Lack\Kindergarden\Kindergarden::addKey(\Lack\Keystore\KeyStore::Get()->getAccessKey("open_ai"));
