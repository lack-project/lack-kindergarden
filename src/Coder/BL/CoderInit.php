<?php

namespace Lack\Kindergarden\Coder\BL;

use Lack\Kindergarden\Cli\Attributes\CliArgument;
use Lack\Kindergarden\Cli\Attributes\CliCommand;
use Lack\Kindergarden\Cli\Attributes\CliParamDescription;
use Lack\Kindergarden\Cli\CliApplication;
use Lack\Kindergarden\Cli\Console;
use Lack\Kindergarden\Cli\ConsoleTrait;
use Lack\Kindergarden\Cog\ConsoleOutputCog;
use Lack\Kindergarden\Cog\ContinueAfterMaxTokensCog;
use Lack\Kindergarden\Cog\DebugInputOutputCog;
use Lack\Kindergarden\Cog\FilesInputCog;
use Lack\Kindergarden\Cog\FrontMatterFormatCog;
use Lack\Kindergarden\Cog\PromptInputCog;
use Lack\Kindergarden\Cog\StringFormatCog;
use Lack\Kindergarden\Cog\StructuredInputCog;
use Lack\Kindergarden\CogWerk\CogWerk;
use Lack\Kindergarden\CogWerk\CogWerkFlavorEnum;
use Lack\Kindergarden\ConfigFile\ConfigFile;
use Lack\Kindergarden\ConfigFile\Type\T_KG_Config_Trunk;
use Lack\Kindergarden\Helper\Frontmatter\FrontmatterFile;

class CoderInit
{
    use ConsoleTrait;
    use CoderEnvironmentTrait;
    public $missingFiles = [];

    #[CliCommand('init', 'Create a .kindergarden.yml file')]
    public function run() {
        if ( ! $this->console->confirm("Create new .kindergarden.yml in cwd?")) {
            return;
        }

        $file = phore_file(".kindergarden.yml");
        if ($file->exists()) {
            $this->console->error("File already exists");
            return;
        }
        $file->set_contents(phore_file(__DIR__ . "/../../../.kindergarden.yml")->get_contents());
        $this->console->info("Created new .kindergarden.yml file");

    }


}
