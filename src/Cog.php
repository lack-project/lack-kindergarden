<?php

namespace Lack\Kindergarden;

use Lack\Kindergarden\Chat\Chat;
use Lack\Kindergarden\Cog\Type\CogMetaData;
use Lack\Kindergarden\Cog\Type\EndOfStream;
use Lack\Kindergarden\Cog\Type\StartOfStream;
use Lack\Kindergarden\Driver\OpenAi\OpenAiRequest;

interface Cog
{

    public function getCogMetaData(): ?CogMetaData;



    public function processChunk(Chat $chat, OpenAiRequest $request, string|StartOfStream|EndOfStream $data, ?callable $next): mixed;


}
