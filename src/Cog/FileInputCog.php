<?php

namespace Lack\Kindergarden\Cog;

use Lack\Kindergarden\Chat\Chat;
use Lack\Kindergarden\Cog\Type\CogMetaData;
use Lack\Kindergarden\Cog\Type\T_InputFile;
use Lack\Kindergarden\Helper\JsonSchemaGenerator;

class FileInputCog extends AbstractCog
{


    public array $files = [];

    public function __construct (
        public readonly string $file,
        public string $name = "files",
        public ?string $instructions = null
    ){

    }


    public function getCogMetaData() : CogMetaData {

        return new CogMetaData(
            name: $this->name,
            instructions: $this->instructions,
            data: phore_file($this->file)->get_contents(),
            schema: null
        );

    }

    public function prepareChat(Chat $chat): void
    {

    }
}
