<?php

namespace Lack\Kindergarden\Cog;

class FilesInputCog extends AbstractInputCog
{


    public function __construct (
        public readonly string $rootPath,
        string $name = "files",
        string $instructions = null
    ){
        parent::__construct($name, $instructions);
    }


    public function addFile(string $filename, string $content)
    {
        file_put_contents($this->path . '/' . $filename, $content);
    }

}
