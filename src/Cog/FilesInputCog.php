<?php

namespace Lack\Kindergarden\Cog;

use Lack\Kindergarden\Chat\Chat;
use Lack\Kindergarden\Cog\Type\T_InputFile;

class FilesInputCog extends AbstractInputCog
{


    public array $files = [];

    public function __construct (
        public readonly string $rootPath,
        string $name = "files",
        string $instructions = null
    ){
        parent::__construct($name, $instructions);
    }


    public function addVirtualFile(string $filename, string|null $content, string|null $instructions = null)
    {
        $this->files[] = new T_InputFile($filename, $content, $instructions);
    }

    public function addFile(string $filename, string|null $instructions = null, bool $includeContent = true)
    {
        // if the filename is absolute (starts with /), check if it is inside the root path otherwise throw an exception
        if (str_starts_with($filename, '/')) {
            if ( ! str_starts_with($filename, $this->rootPath)) {
                throw new \Exception("File $filename is not inside the root path $this->rootPath");
            }
            // Convert to relative path
            $filename = substr($filename, strlen($this->rootPath));
            // Strip the leading slash
            $filename = ltrim($filename, '/');
        }
        // Check if File exists
        $absoluteFile = $this->rootPath . "/" . $filename;

        if ( ! file_exists($absoluteFile)) {
            throw new \Exception("File $absoluteFile does not exist.");
        }
        $content = null;
        if ($includeContent) {
            $content = file_get_contents($absoluteFile);
            if ($content === false) {
                throw new \Exception("Could not read file $absoluteFile");
            }
        }
        $this->files[] = new T_InputFile($filename, $content, $instructions);
    }

    public function addFiles(array|string $includeFilters, array|string $excludeFilters, bool $includeContent = true)
    {

        $directory = new \RecursiveDirectoryIterator($this->rootPath);
        $iterator = new \RecursiveIteratorIterator($directory);

        $files = new \RegexIterator($iterator, '/^.+$/i', \RecursiveRegexIterator::GET_MATCH);
        foreach ($files as $file) {
            // check if it is a binary file

            $filename = $file[0];
            $relativeFilename = substr($filename, strlen($this->rootPath));
            $relativeFilename = ltrim($relativeFilename, '/');
            $include = true;
            foreach ($includeFilters as $filter) {
                if ( ! fnmatch($filter, $relativeFilename)) {
                    $include = false;
                    break;
                }
            }
            foreach ($excludeFilters as $filter) {
                if (fnmatch($filter, $relativeFilename)) {
                    $include = false;
                    break;
                }
            }
            if ($include) {
                $this->addFile($filename, null, $includeContent);
            }
        }
    }


    public function prepareChat(Chat $chat): void
    {
        // TODO: Implement prepareChat() method.
    }
}
