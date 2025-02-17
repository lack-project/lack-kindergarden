<?php

namespace Lack\Kindergarden\Coder\BL;

use Lack\Kindergarden\Cli\Attributes\CliArgument;
use Lack\Kindergarden\Cli\Attributes\CliParamDescription;
use Lack\Kindergarden\Cog;
use Lack\Kindergarden\Cog\FilesInputCog;
use Lack\Kindergarden\ConfigFile\ConfigFile;
use Lack\Kindergarden\ConfigFile\Type\T_KG_Config_Trunk;

trait CoderEnvironmentTrait
{

    #[CliParamDescription("The environment to use")]
    public ?string $env = null;


    public ?string $configFile = ".kindergarden.yml";


    /**
     * @return ConfigFile<T_KG_Config_Trunk>
     * @throws \Lack\Kindergarden\ConfigFile\ConfigFileNotFoundException
     */
    protected function getConfigFile() : ConfigFile
    {
        $config = new ConfigFile($this->configFile, T_KG_Config_Trunk::class);
        return $config;
    }


    /**
     * @return Cog[]
     * @throws \Lack\Kindergarden\ConfigFile\ConfigFileNotFoundException
     */
    protected function getConfigFileCogs() : array
    {
        $configFile = $this->getConfigFile();
        $configEnv = $configFile->getConfig()->environments;
        $return = [];

        if ($this->env === "none") {
            return [];
        }

        // Include code Files
        if (isset ($configEnv["coder"])) {
            $coder = $configEnv["coder"];
            foreach ($coder->files as $file) {
                $curFileCog = new FilesInputCog($configFile->getConfigFilePath(), $file->name, $file->instructions);
                $curFileCog->addFiles($file->include, $files->exclude ?? []);
                $return[] = $curFileCog;
            }
        }

        return $return;
    }

}
