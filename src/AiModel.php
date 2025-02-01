<?php

namespace Lack\Kindergarden;

use Lack\Kindergarden\Driver\OpenAi\OpenAiClient;

interface AiModel
{


    public function getClient() : OpenAiClient;

}
