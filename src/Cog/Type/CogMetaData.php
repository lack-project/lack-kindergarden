<?php

namespace Lack\Kindergarden\Cog\Type;

use Lack\Kindergarden\Chat\ChatResponseFormat;

class CogMetaData
{

    public function __construct(
        /**
         * The name of this Cog and how it is referenced by other Cogs
         * @var string
         */
        public ?string $name = null,

        /**
         * Alternative names for this Cog
         * @var array
         */
        public ?array $aliases = null,

        /**
         * A description of what this Cog does
         * @var string
         */
        public ?string $instructions = null,

        /**
         * The actual Data
         * @var mixed
         */
        public mixed $data = null,

        /**
         * The schema of the data
         * @var string
         */
        public ?array              $schema = null,






        public ?string             $systemPrompt = null,

        public ?string             $userPrompt = null,

        public ?ChatResponseFormat $responseFormat = null

    ){}

    public function getCogInputStruct() : ?array
    {
        $internalParams = ["assistantPrompt", "userPrompt", "responseFormat"];

        $params = get_object_vars($this);
        $params = array_diff_key($params, array_flip($internalParams));

        // Return key => value array for all not internal params that are not null
        return array_filter($params, fn($value) => $value !== null);

    }

}
