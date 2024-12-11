<?php

namespace Lack\Kindergarden;

use Lack\Kindergarden\Chat\Chat;

interface CogToChatSerializer
{

    public function transformToChat(array $inputCogs, OutputCog $outputCog): Chat;

}
