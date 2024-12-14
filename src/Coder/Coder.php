<?php

namespace Lack\Kindergarden\Coder;

use Lack\Kindergarden\Cli\Attributes\CliArgument;
use Lack\Kindergarden\Cli\Attributes\CliCommand;
use Lack\Kindergarden\Cli\Attributes\CliParamDescription;
use Lack\Kindergarden\Cog\FilesInputCog;
use Lack\Kindergarden\Helper\CogInputMessageGenerator;

class Coder
{



    #[CliCommand('coder:export', 'create a prompt including files')]
    #[CliArgument('files', 'one or more files, glob-expressions', true)]
    public function coder_prepare(array $argv, #[CliParamDescription("List all files")]bool $list = false): void{
        $filesCog = new FilesInputCog(getcwd(), "files", "Serialized files and content. These files should be considered as input files.");

        $files = [];
        array_map(function($arg) use (&$files) {
            $files = array_merge($files, glob($arg) ?? throw new \Exception("Could not find any files matching $arg"));
        }, $argv);

        $files = array_values(array_unique($files));
        array_map(fn($file) => $filesCog->addFile($file), $files);

        if ($list) {
            foreach ($filesCog->debugGetFileList() as $file) {
                echo $file . "\n";
            }
            echo "Total: " . count($filesCog->debugGetFileList()) . " files\n";
            return;
        }



        $serializer = new CogInputMessageGenerator([$filesCog]);
        echo $serializer->getMessage() . "\n";

    }

}
