<?php

namespace Lack\Kindergarden;

use Lack\Kindergarden\Driver\OpenAi\OpenAiClient;

class Kindergarden
{

    const DEFAULT_MODEL = OpenAiClient::OPENAI_DEFAULT_MODEL;
    const DEFAULT_REASONING_MODEL = OpenAiClient::OPENAI_DEFAULT_REASONING_MODEL;

    private static $apiKeys = [];

    public static function addKey(string $apiKey, string $instance = "default"): void
    {
        self::$apiKeys = [$instance => $apiKey];
    }


    public static function defaults(string $defaultModel = self::DEFAULT_MODEL): Kindergarden
    {
        return new Kindergarden($defaultModel, self::$apiKeys["default"] ?? throw new \Exception("No default api key set"));
    }

    public function __construct (string $defaultModel = null, string $apiKey = null)
    {
        $this->defaultModel = $defaultModel;
        $this->apiKey = $apiKey;

    }


    public function getClient(): OpenAiClient
    {
        return new OpenAiClient($this->apiKey, $this->defaultModel);
    }


    public function getDefaultReasoningModel(): string
    {
        return self::DEFAULT_REASONING_MODEL;
    }


    public function getDefaultModel(): string
    {
        return self::DEFAULT_MODEL;
    }

}
