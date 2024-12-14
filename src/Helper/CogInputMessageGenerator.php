<?php

namespace Lack\Kindergarden\Helper;

use Lack\Kindergarden\InputCog;

class CogInputMessageGenerator
{

    public function __construct(
        /**
         * @var InputCog[]
         */
        public array $cogs = []
    ) {
    }


    public function addCog(InputCog $cog): void
    {
        $this->cogs[] = $cog;
    }


    public function getMessage() : string {
        $message = file_get_contents(__DIR__ . "/CogInputMessageGeneratorPrompt.txt");
        $data = [];
        foreach ($this->cogs as $cog) {
            $data[] = $cog->getData();
        }
        return $message . "\n\n```json\n" . json_encode($data) . "\n```";
    }

}
