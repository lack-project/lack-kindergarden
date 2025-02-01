<?php

namespace Lack\Kindergarden\Cog;

use Lack\Kindergarden\Chat\Chat;
use Lack\Kindergarden\Cog\Type\EndOfStream;
use Lack\Kindergarden\Cog\Type\StartOfStream;
use Lack\Kindergarden\Driver\OpenAi\OpenAiRequest;

class StringFormatCog extends AbstractCog
{

    private $data = "";

    public function processChunk(Chat $chat, OpenAiRequest $request, string|StartOfStream|EndOfStream $data, ?callable $next): mixed
    {
        if (is_string($data)) {
            $this->data .= $data;
        }
        return $next($data);
    }


    public function __toString(): string
    {
        return $this->data;
    }

}
