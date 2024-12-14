<?php

namespace Lack\Kindergarden\Cli\Coder;

use Lack\Kindergarden\Cli\Attributes\CliArgument;
use Lack\Kindergarden\Cli\Attributes\CliCommand;
use Lack\Kindergarden\Cli\Attributes\CliParamDescription;

class Coder
{



    #[CliCommand('coder:prepare', 'Prepare the coder')]
    #[CliArgument('name', 'Name of the argument', true)]
    public function coder_prepare(
        #[CliParamDescription('The name of the coder')]
        string $name,

        #[CliParamDescription('The name of the coder')]
        string $wurstbrot,
        array $argv

    ) {
        echo "Preparing coder $name\n";
        print_r ($argv);
    }

}
