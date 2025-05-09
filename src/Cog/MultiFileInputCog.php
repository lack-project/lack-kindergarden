<?php

namespace Lack\Kindergarden\Cog;

use Lack\Kindergarden\Chat\Chat;
use Lack\Kindergarden\Cog\Type\CogMetaData;
use Lack\Kindergarden\Cog\Type\T_InputFile;
use Lack\Kindergarden\Helper\JsonSchemaGenerator;

class MultiFileInputCog extends AbstractCog
{


    public array $files = [];

    public function __construct (
        public readonly string $rootPath,
        public string $name = "files",
        public ?string $instructions = null
    ){

    }


    public function addVirtualFile(string $filename, string|null $content, string|null $instructions = null)
    {
        $this->files[] = new T_InputFile($filename, $content, $instructions);
    }

    public function addFile(string $filename, string|null $instructions = null, bool $includeContent = true)
    {

        if ( ! file_exists($filename)) {
            throw new \Exception("File '$filename' does not exist.");
        }
        $content = null;
        if ($includeContent) {
            $content = file_get_contents($filename);
            if ($content === false) {
                throw new \Exception("Could not read file '$filename'");
            }
        }
        $this->files[] = new T_InputFile($filename, $content, $instructions);
    }

    public function addFiles(array|string $includeFilters, array|string $excludeFilters = [], bool $includeContent = true)
    {

        foreach (phore_glob($includeFilters, $excludeFilters) as $filename) {
            echo "Adding file: $filename\n";
            $this->addFile($filename, null, $includeContent);
        }
    }


    public function getCogMetaData() : CogMetaData {

        return new CogMetaData(
            name: $this->name,
            instructions: $this->instructions,
            data: $this->files,
            schema: JsonSchemaGenerator::buildSchema(T_InputFile::class)
        );

    }


    /**
     * @return string[]
     */
    public function debugGetFileList() : array {
        return array_map(fn($file) => $file->filename, $this->files);
    }

    public function prepareChat(Chat $chat): void
    {

    }
}
