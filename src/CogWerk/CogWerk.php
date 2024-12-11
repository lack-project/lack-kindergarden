<?php

namespace Lack\Kindergarden\CogWerk;

use Lack\Kindergarden\InputCog;
use Lack\Kindergarden\OutputCog;

class CogWerk
{

    /**
     * @var InputCog[]
     */
    private array $inputCogs = [];
    private OutputCog $outputCog;


    public function setOutputCog(OutputCog $outputCog): CogWerk
    {
        $this->outputCog = $outputCog;
        return $this;
    }

    public function addInputCog(InputCog $inputCog): CogWerk
    {
        $this->inputCogs[] = $inputCog;
        return $this;
    }


}
