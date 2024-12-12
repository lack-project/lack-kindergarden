<?php

namespace Lack\Kindergarden\Cog;

use Lack\Kindergarden\Chat\Chat;
use Lack\Kindergarden\OutputCog;

class StructuredOutputCog implements OutputCog
{


    public function __construct(private string|null $instructions = null)
    {

    }

    public function addStructuredOutputSchema(string $key, string|array $schema, string|null $instructions = null): StructuredOutputCog
    {
        return $this;
    }


    public function addStructuredOutputKey(string $key, string $type, string|null $instructions = null): StructuredOutputCog
    {
        return $this;
    }

    public function prepareChat(Chat $chat): void
    {

    }
}
