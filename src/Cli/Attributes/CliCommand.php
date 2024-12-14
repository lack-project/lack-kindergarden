<?php

namespace Lack\Kindergarden\Cli\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD)]
class CliCommand {
    public function __construct(
        public string $name,
        public string $description = ''
    ) {}
}
