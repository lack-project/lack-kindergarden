<?php

namespace Lack\Kindergarden\Chat;

class JsonChatResponseFormat implements ChatResponseFormat
{

    public function __construct(
        public readonly string $jsonSchema)
    {

    }

}
