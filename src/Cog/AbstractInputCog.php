<?php

namespace Lack\Kindergarden\Cog;

abstract class AbstractInputCog
{

    public function __construct(
        private ?string $name,
        private ?string $instructions = null
    )
    {
    }


    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }


    /**
     * Instructions for the AI to follow
     *
     * Can have %name% placeholder for the name of the cog
     *
     * @param string $instructions
     * @return void
     */
    public function setInstructions(string $instructions): void
    {
        $this->instructions = $instructions;
    }

    public function getInstructions(): string
    {
        return $this->instructions;
    }


}
