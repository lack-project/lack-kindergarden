<?php

namespace Lack\Kindergarden\Coder\BL;

use Lack\Kindergarden\Cli\Attributes\CliArgument;
use Lack\Kindergarden\Cli\Attributes\CliCommand;
use Lack\Kindergarden\Cli\ConsoleTrait;
use Lack\Kindergarden\Cog\ContinueAfterMaxTokensCog;
use Lack\Kindergarden\Cog\CreateModifyFileCog;
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
use Lack\Kindergarden\Helper\Frontmatter\FrontmatterException;
use Lack\Kindergarden\Models\Model;

class CoderRun
{
    use ConsoleTrait;
    use CoderEnvironmentTrait;

    public $content = null;
    /**
     * @var T_PrepareMetaData|null
     */
    public T_PrepareMetaData|null $header = null;

    #[CliCommand('coder:run', 'Generate each file from the prepared file')]
    #[CliArgument('file', 'The markdown prepare file', true)]
    public function run(array $argv) {
        $file = $argv[0];


        $this->parseFile(file_get_contents($file) ?? throw new \Exception("Could not read file $file"));

        $modifiedFilesCogs = [];
        $modifiedFiles = [];
        foreach ($this->header->editFiles as $file) {
            $this->console->info("Editing file $file");

            // Add the files after each turn
            $filesCog = new MultiFileInputCog(getcwd(), "files", "Already existing serialized files and content referenced within the programming-prompt.");

            foreach ($this->header->includeFiles as $inlcudeFile) {
                if (is_file($inlcudeFile)) {
                    $filesCog->addFile($inlcudeFile);
                }
            }

            $alreadyModifiedFiles = new MultiFileInputCog(getcwd(), "already-modified-files", "Already modified files (use these as reference for changes in the current file).");
            foreach ($modifiedFiles as $modifiedFile) {
                $alreadyModifiedFiles->addFile($modifiedFile);
            }

            $cogwerk = new CogWerk(Model::DEFAULT_MODEL);

            $cogwerk->addCog(new ContinueAfterMaxTokensCog());
            $cogwerk->addCog($filesCog);


            foreach ($this->getConfigFileCogs() as $cog) {
                $cogwerk->addCog($cog);
            }


            $cogwerk->addCog(new StructuredInputCog("programming-instructions", file_get_contents(__DIR__ . "/run_instructions.txt"), "Follow this additional instructions."));
            $cogwerk->addCog(new DebugInputOutputCog());

            $cogwerk->run($cmfc = new CreateModifyFileCog($file, "original-file-content", "Follow the instructions provided below for the file $file: " . $this->content));

            $modifiedFilesCogs[] = $cmfc;

            $this->console->info("DONE: $file has been modified");
            $modifiedFiles[] = $file;
        }
        $this->console->success("All files have been modified:");
        foreach ($modifiedFiles as $file) {
            $this->console->success($file);
        }

        if ($this->console->confirm("Keep the files?", true)) {
            foreach ($modifiedFilesCogs as $file) {
                $file->keep();
            }
        } else {
            foreach ($modifiedFilesCogs as $file) {
                $file->undo();
            }
        }
    }


    private function parseFile(string $content) {
        $raw = $content;
        if (trim($raw) === '') {
            throw new FrontmatterException("Empty file");
        }
        $parts = preg_split('/^-{3}\s*$/m', $raw, 3);
        if (count($parts) < 3) {
            throw new FrontmatterException("Invalid frontmatter format");
        }

        $this->header = phore_hydrate($this->parseYaml($parts[1]), T_PrepareMetaData::class);
        $this->content = ltrim($parts[2]);
    }

    private function parseYaml(string $yaml): array
    {
        if ( ! function_exists('yaml_parse')) {
            throw new FrontmatterException("YAML extension not available.");
        }
        $parsed = phore_yaml_decode($yaml);

        if (!is_array($parsed)) {
            throw new FrontmatterException("YAML parse error");
        }
        return $parsed;
    }


}
