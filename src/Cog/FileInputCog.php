<?php

namespace Lack\Kindergarden\Cog;

use http\Client\Curl\User;
use Lack\Kindergarden\Chat\Chat;
use Lack\Kindergarden\Cog\Type\CogMetaData;
use Lack\Kindergarden\Cog\Type\T_InputFile;
use Lack\Kindergarden\Cog\Type\UserInspectableCog;
use Lack\Kindergarden\Helper\JsonSchemaGenerator;

class FileInputCog extends AbstractCog implements UserInspectableCog
{


    public array $files = [];

    public function __construct (
        public readonly string $file,
        public string $name = "files",
        public ?string $instructions = null
    ){

    }


    public function getCogMetaData() : CogMetaData {

        if ( ! file_exists($this->file)) {
            throw new \Exception("File '$this->file' does not exist. (Defined in FileInputCog name: '$this->name')");
        }
        $content = file_get_contents($this->file);
        if ($content === false) {
            throw new \Exception("Could not read file '$this->file'");
        }

        return new CogMetaData(
            name: $this->name,
            instructions: $this->instructions,
            data: $content,
            schema: null
        );

    }

    public function prepareChat(Chat $chat): void
    {

    }

    public function getUserDebugInfo(): string
    {
        $ret = "FileInputCog: " . $this->name . "\n";
        $ret .= "  + " . $this->file . "\n";
        return $ret;
    }
}
