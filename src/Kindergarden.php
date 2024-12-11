<?php

namespace Lack\Kindergarden;

class Kindergarden
{

    public static function defaults(string $defaultModel = 'gpt-4o'): Kindergarden
    {
        return new Kindergarden($defaultModel);
    }

    public function __construct (string $defaultModel = null, string $apiKey = null)
    {
        $this->defaultModel = $defaultModel;
        $this->apiKey = $apiKey;

    }




}
