<?php

namespace Lack\Kindergarden\Cog;

use Lack\Kindergarden\Cog\Type\CogMetaData;

class PromptInputCog extends AbstractCog
{

    public function __construct(
        public ?string $systemPrompt = null,
        public ?string $userPrompt = null,
    )
    {}


    public function getCogMetaData(): CogMetaData
    {
        return new CogMetaData(
            systemPrompt: $this->systemPrompt,
            userPrompt: $this->userPrompt,
            responseFormat: null,
        );
    }
}
