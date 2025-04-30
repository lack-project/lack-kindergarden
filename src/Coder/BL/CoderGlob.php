<?php

namespace Lack\Kindergarden\Coder\BL;

use Lack\Kindergarden\Cli\Attributes\CliArgument;
use Lack\Kindergarden\Cli\Attributes\CliCommand;
use Lack\Kindergarden\Cli\Attributes\CliParamDescription;
use Lack\Kindergarden\Cli\CliApplication;
use Lack\Kindergarden\Cli\ConsoleTrait;
use Lack\Kindergarden\Cog\ConsoleOutputCog;
use Lack\Kindergarden\Cog\ContinueAfterMaxTokensCog;
use Lack\Kindergarden\Cog\DebugInputOutputCog;
use Lack\Kindergarden\Cog\MultiFileInputCog;
use Lack\Kindergarden\Cog\FrontMatterFormatCog;
use Lack\Kindergarden\Cog\PromptInputCog;
use Lack\Kindergarden\Cog\StringFormatCog;
use Lack\Kindergarden\Cog\StructuredInputCog;
use Lack\Kindergarden\CogWerk\CogWerk;
use Lack\Kindergarden\CogWerk\CogWerkFlavorEnum;
use Lack\Kindergarden\ConfigFile\ConfigFile;
use Lack\Kindergarden\ConfigFile\Type\T_KG_Config_Trunk;
use Lack\Kindergarden\Helper\Frontmatter\FrontmatterFile;

class CoderGlob
{
    use ConsoleTrait;
    public $missingFiles = [];

    #[CliCommand('coder:glob', 'List files matching a glob pattern (use ** for recursive search)')]
    #[CliArgument('pattern', 'the prompt including files to include', true)]
    public function run(array $argv, #[CliParamDescription("Enable Reasoning (costly)")] bool $reasoning = false) {
        $path = $argv[0];


        $config = new ConfigFile(".kindergarden.yml", T_KG_Config_Trunk::class);
        print_r($config->getConfig());

        print_r (phore_glob($path));

    }


}
