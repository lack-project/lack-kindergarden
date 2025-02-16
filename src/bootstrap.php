<?php



$app = \Lack\Kindergarden\Cli\CliApplication::getInstance();
$app->node()->group("coder", "Coder commands");

$app->registerClass(\Lack\Kindergarden\Coder\Coder::class);
$app->registerClass(\Lack\Kindergarden\Coder\BL\CoderPrepare::class);
$app->registerClass(\Lack\Kindergarden\Coder\BL\CoderRun::class);

if (class_exists(\Lack\Keystore\KeyStore::class)) {
    \Lack\Kindergarden\Kindergarden::addKey(\Lack\Keystore\KeyStore::Get()->getAccessKey("open_ai"));
} elseif (getenv("OPENAI_API_KEY") !== false) {
    \Lack\Kindergarden\Kindergarden::addKey("open_ai", getenv("OPENAI_API_KEY"));
} else {
    throw new \Exception("No OpenAI API key found. Please provide it in the environment variable 'OPENAI_API_KEY' or by installing the lack/keystore package.");
}



