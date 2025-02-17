<?php

namespace Lack\Kindergarden\ConfigFile\Type;

class T_KG_Config_Trunk
{

    public string $version = "1.0";


    /**
     * @var array<string, T_KG_Config_Environmnent>
     */
    public array $environments = [];

}
