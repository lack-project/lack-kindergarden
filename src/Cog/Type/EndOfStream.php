<?php

namespace Lack\Kindergarden\Cog\Type;

class EndOfStream
{

    public function __construct (public readonly bool $completedNaturally = true)
    {
    }

}
