<?php

namespace Lack\Kindergarden\Models;

use Lack\Kindergarden\Driver\OpenAi\OpenAiClient;
use Lack\Kindergarden\Kindergarden;

class ModelHandler {

    public function __construct(
        protected readonly string    $model,
        protected readonly string   $provider,
    ) {
        // You can initialize any properties or dependencies here if needed
    }

    public function getClient() : OpenAiClient
    {
        return new OpenAiClient(
            apiKey: Kindergarden::getKey($this->provider),
        );
    }

    /**
     * Return the model string for the current model. This string can be passed
     * to the API to use the model.
     *
     * @return string
     * @throws \Exception
     */
    public function getModelString() : string
    {
        return $this->model;
    }


}
