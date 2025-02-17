<?php

namespace Lack\Kindergarden\Coder\BL;

use Lack\Kindergarden\Cli\Attributes\CliArgument;
use Lack\Kindergarden\Cli\Attributes\CliCommand;
use Lack\Kindergarden\Cli\Attributes\CliParamDescription;
use Lack\Kindergarden\Cli\CliApplication;
use Lack\Kindergarden\Cli\ConsoleTrait;
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

class CoderPrepare
{
    use ConsoleTrait;
    use CoderEnvironmentTrait;
    public $missingFiles = [];

    #[CliCommand('coder:prepare', 'prepare a new programming task')]
    #[CliArgument('prompt', 'the prompt including files to include', true)]
    public function run(array $argv, #[CliParamDescription("Enable Reasoning (costly)")] bool $reasoning = false) {
        $programmingPrompt = $argv;
        $filesCog = new FilesInputCog(getcwd(), "files", "Already existing serialized files and content referenced within the programming-prompt.");

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
        $cogwerk->addCog(new PromptInputCog("Your job is to plan / prepare the task provided as user-prompt. Follow the guides provided as programming-prepare-instructions.", $programmingPrompt));
        $cogwerk->addCog(new StructuredInputCog("programming-prepare-instructions", file_get_contents(__DIR__ . "/prepare_instructions.txt"), "Follow the"));

        foreach ($this->getConfigFileCogs() as $cog) {
            $cogwerk->addCog($cog);
        }



        $cogwerk->addCog(new DebugInputOutputCog());
        $frontmatter = $cogwerk->run(new FrontMatterFormatCog(T_PrepareMetaData::class));
        /* @var $frontmatter FrontMatterFormatCog<T_PrepareMetaData> */
        $frontmatter->getHeader()->original_prompt = $programmingPrompt;


        // Determine next filename prefix
        $files = glob("kg-*-*.md");
        $nextNum = 0;
        foreach ($files as $file) {
            $parts = explode("-", basename($file));
            $num = (int) $parts[1];
            if ($num > $nextNum) {
                $nextNum = $num;
            }
        }
        $outFile = "kg-" . str_pad($nextNum + 1, 3, "0", STR_PAD_LEFT) . "-" . $frontmatter->getHeader()->slugName . ".md";

        file_put_contents($outFile, $frontmatter->__toString());

        $this->console->success("Task prepared and saved to $outFile");
        if ($this->console->ask("Run changes? (Y/n)", "y") === "y") {
            CliApplication::getInstance()->run(["coder", "run", $outFile]);
        }
    }


}
