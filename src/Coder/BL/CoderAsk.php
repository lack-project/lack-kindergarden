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
use Lack\Kindergarden\Cog\FilesInputCog;
use Lack\Kindergarden\Cog\FrontMatterFormatCog;
use Lack\Kindergarden\Cog\PromptInputCog;
use Lack\Kindergarden\Cog\StringFormatCog;
use Lack\Kindergarden\Cog\StructuredInputCog;
use Lack\Kindergarden\CogWerk\CogWerk;
use Lack\Kindergarden\CogWerk\CogWerkFlavorEnum;
use Lack\Kindergarden\Helper\Frontmatter\FrontmatterFile;

class CoderAsk
{
    use ConsoleTrait;
    public $missingFiles = [];

    #[CliCommand('coder:ask', 'Answer the question about the programming task')]
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
        $cogwerk->addCog(new PromptInputCog("Your job is to answer the following question about the files provided.", $programmingPrompt));

        // Output the result to the console
        $cogwerk->run(new ConsoleOutputCog());

        $this->console->success("Task ended successfully.");
    }


}
