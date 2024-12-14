<?php

namespace Lack\Kindergarden\Cli;

trait ConsoleTrait
{

    protected Console $console;

    public function __set_console($console)
    {
        $this->console = $console;
    }

}
