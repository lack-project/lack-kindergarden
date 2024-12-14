<?php

namespace Lack\Kindergarden\Cli\Attributes;

/**
 * @Annotation
 *
 * CliArgument is available as $argv in method.
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class CliArgument
{
    public function __construct(
        public string $name,
        public string $description = '',
        public bool $required = false,
        public bool $multiple = false
    ) {}

}
