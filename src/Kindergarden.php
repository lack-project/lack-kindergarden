<?php

namespace Lack\Kindergarden;

use Lack\Kindergarden\Driver\OpenAi\OpenAiClient;
use Lack\Kindergarden\Models\Model;

class Kindergarden
{


    private static $apiKeys = [
        "openai" => null,
        "anthropic" => null,
    ];

    public static function addKey(string $provider, string $apiKey): void
    {
        self::$apiKeys[$provider] = $apiKey;
    }


    public static function getKey(string $provider): ?string
    {
        return self::$apiKeys[$provider] ?? throw new \Exception("API key for provider '$provider' not found.");
    }


    public static function defaults(): Kindergarden
    {
        return new Kindergarden();
    }



    public function __construct ()
    {

    }



}
