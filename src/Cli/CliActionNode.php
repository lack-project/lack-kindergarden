<?php

namespace Lack\Kindergarden\Cli;

class CliActionNode extends CliGroupNode
{
    public ?\Closure $handler = null;
    public array $arguments = []; // [['name'=>...,'desc'=>...,'required'=>...],...]
    public array $options = [];


    public function command(string $name, string $description = ''): self
    {
        throw new \InvalidArgumentException('Cannot create a command in an action node');
    }
    public function argument(string $name, string $description = '', bool $required = false, bool $multiple = false): self
    {
        $this->arguments[] = ['name' => $name, 'desc' => $description, 'required' => $required, "multiple" => $multiple];
        return $this;
    }

    public function option(string $name, string $short = '', bool $hasValue = false, string $description = '', bool $required = false): self
    {
        $this->options[] = ['name' => $name, 'short' => $short, 'value' => $hasValue, 'desc' => $description, 'required' => $required];
        return $this;
    }

    public function handler(callable $handler): self
    {
        $this->handler = $handler;
        return $this;
    }
}
