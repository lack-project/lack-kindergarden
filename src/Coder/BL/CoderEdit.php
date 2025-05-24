<?php

namespace Lack\Kindergarden\Coder\BL;

use Lack\Kindergarden\Cli\Attributes\CliArgument;
use Lack\Kindergarden\Cli\Attributes\CliCommand;
use Lack\Kindergarden\Cli\Attributes\CliParamDescription;
use Lack\Kindergarden\Cli\CliApplication;
use Lack\Kindergarden\Cli\ConsoleTrait;
use Lack\Kindergarden\Cog\ContinueAfterMaxTokensCog;
use Lack\Kindergarden\Cog\CreateModifyFileCog;
use Lack\Kindergarden\Cog\DebugInputOutputCog;
use Lack\Kindergarden\Cog\FileInputCog;
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

class CoderEdit
{
    use ConsoleTrait;
    use CoderEnvironmentTrait;
    public $missingFiles = [];

    #[CliCommand('coder:edit', 'Edit/Prompt a single file')]
    #[CliArgument('[file] [prompt]', 'The file to edit followed by the prompt including files to include', true)]
    public function run(array $argv, #[CliParamDescription("Enable Reasoning (costly)")] bool $reasoning = false, #[CliParamDescription("Do not add Context from config file")] bool $nocontext = false) {
        $programmingPrompt = $argv;
        $filesCog = new MultiFileInputCog(getcwd(), "files", "Already existing serialized files and content referenced within the programming-prompt.");

        $editFile = array_shift($programmingPrompt);


        foreach ($programmingPrompt as $part) {
            if (is_file($part)) {
                $filesCog->addFile($part);
            }
            // if it is a pattern like dir/*.php or dir/**/*.php
            else if (strpos($part, '*') !== false) {
                $files = glob($part);
                if ($files === false) {
                    throw new \Exception("Could not find any files matching $part");
                }
                foreach ($files as $file) {
                    $filesCog->addFile($file);
                }
            }
            else {
                $this->missingFiles[] = $part;
            }

        }

        $programmingPrompt = implode(" ", $programmingPrompt);

        $cogwerk = new CogWerk($reasoning ? CogWerkFlavorEnum::REASONING : CogWerkFlavorEnum::DEFAULT);
        $cogwerk->addCog(new ContinueAfterMaxTokensCog());
        $cogwerk->addCog($filesCog);
        $cogwerk->addCog(new PromptInputCog("Your job is to modify the content of the @original-file-content according to the @user-instructions. You follow the instructions and return the full content of the file.", "Edit the content of @original-file-content according to @user-instructions and output it."));
        $cogwerk->addCog(new StructuredInputCog("@user-instructions", $programmingPrompt, "The instructions on how to modify the @original-file-content."));
        if (!$nocontext) {
            foreach ($this->getConfigFileCogs() as $cog) {
                $cogwerk->addCog($cog);
            }
        }


        $this->console->info($cogwerk->getUserDebugInfo());
        

        $cogwerk->addCog(new DebugInputOutputCog());
        $cogwerk->run($modifyCog = new CreateModifyFileCog($editFile, "@original-file-content", "This is the file content to modify."));

        sleep (2);
        $this->console->writeln("File: $editFile");
        if ( ! $this->console->confirm("Keep changes?", true)) {
            $this->console->writeln("Changes discarded.");
            $modifyCog->undo();
        } else {
            $modifyCog->keep();
        }

    }


}
