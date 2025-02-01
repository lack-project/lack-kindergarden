<?php

namespace Lack\Kindergarden\Cog;

use Lack\Kindergarden\Cog\Type\CogMetaData;

class StructuredInputCog extends AbstractCog
{

    public function __construct(public string $name, public mixed $data, private string|null $instructions = null)
    {

    }


    public function getCogMetaData(): CogMetaData
    {
        return new CogMetaData(
            name: $this->name,
            instructions: $this->instructions,
            data: $this->data
        );
    }
}
