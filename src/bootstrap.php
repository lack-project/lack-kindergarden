<?php

namespace Lack\Kindergarden;

use Lack\Kindergarden\Coder\BL\CoderAsk;
use Lack\Kindergarden\Coder\BL\CoderEdit;
use Lack\Kindergarden\Coder\BL\CoderGlob;
use Lack\Kindergarden\Coder\BL\CoderInit;
use Lack\Kindergarden\Coder\BL\CoderPrepare;
use Lack\Kindergarden\Coder\BL\CoderRun;
use Lack\Kindergarden\Coder\Coder;

$app = \Lack\Kindergarden\Cli\CliApplication::getInstance();
$app->node()->group("coder", "Coder commands");

$app->registerClass(CoderInit::class);
$app->registerClass(CoderPrepare::class);
$app->registerClass(CoderRun::class);
$app->registerClass(CoderAsk::class);
$app->registerClass(CoderGlob::class);
$app->registerClass(CoderEdit::class);

if (class_exists(\Lack\Keystore\KeyStore::class)) {
    \Lack\Kindergarden\Kindergarden::addKey("open_ai", \Lack\Keystore\KeyStore::Get()->getAccessKey("open_ai"));
} elseif (getenv("OPENAI_API_KEY") !== false) {
    \Lack\Kindergarden\Kindergarden::addKey("open_ai", getenv("OPENAI_API_KEY"));
} else {
    throw new \Exception("No OpenAI API key found. Please provide it in the environment variable 'OPENAI_API_KEY' or by installing the lack/keystore package.");
}



