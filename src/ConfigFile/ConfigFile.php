<?php

namespace Lack\Kindergarden\ConfigFile;

/**
 * @template T
 */
class ConfigFile
{

    private ?string $configFilePathAbsolute = null;
    private ?string $cast;

    private string $rootDir;

    /**
     * @param string $filename
     * @param class-string<T>|null $cast
     * @param bool $searchInParent
     * @throws ConfigFileNotFoundException
     */
    public function __construct (string $filename, string $cast, bool $searchInParent = true) {
        // If the filename contains slashes, it is a path to a file otherwise search in current and parent directories for this file
        $searchFile = str_contains($filename, "/");

        $configFilePathAbsolute = $searchFile ? $filename : $this->searchInParent(getcwd(), $filename);
        if ($configFilePathAbsolute === "") {
            return;
        }

        $this->configFilePathAbsolute = $configFilePathAbsolute;
        $this->cast = $cast;
    }


    private function searchInParent(string $dir, string $filename): string {

        if (file_exists($dir . "/" . $filename)) {
            return $dir . "/" . $filename;
        }
        if ($dir === "/") {
            return "";
        }
        return $this->searchInParent(dirname($dir), $filename);
    }

    public function getConfigFileName(): ?string {
        return $this->configFilePathAbsolute;
    }

    public function getConfigFilePath(): ?string {
        return dirname($this->configFilePathAbsolute);
    }




    /**
     * Return the parsed config file or an empty instance of the cast class
     *
     * @return T
     */
    public function getConfig() {
        static $config = $this->configFilePathAbsolute !== null ? phore_hydrate_file($this->configFilePathAbsolute, $this->cast) : new ($this->cast)();
        return $config;
    }

}
