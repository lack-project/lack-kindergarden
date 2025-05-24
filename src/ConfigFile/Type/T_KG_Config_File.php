<?php

namespace Lack\Kindergarden\ConfigFile\Type;

class T_KG_Config_File
{

    public string $name;

    public bool $enabled = true;
    
    /**
     * @var string[]
     */
    public array $include;

    /**
     * @var string[]|null
     */
    public ?array $exclude = null;

    public string $instructions;
}
