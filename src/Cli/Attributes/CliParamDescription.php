<?php

namespace Lack\Kindergarden\Cli\Attributes;


#[\Attribute(\Attribute::TARGET_PARAMETER | \Attribute::TARGET_PROPERTY |\Attribute::IS_REPEATABLE)]
class CliParamDescription {
    public function __construct(
        public string $description = ''
    ) {}
}
