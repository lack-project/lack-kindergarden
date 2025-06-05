<?php

namespace Lack\Kindergarden\Models;

enum Model: string
{

    case DEFAULT_MODEL = "default";

    case DEFAULT_REASONING_MODEL = "default-reasoning";

    case GPT_O3_CURRENT = "o3";

    case GPT_O3 = "o3-2025-04-16";
    case GPT_4O_2024_08_06 = "gpt-4o-2024-08-06";
    case GPT_41_2025_04_14 = "gpt-4.1-2025-04-14";

    public static function normalize(self|string $value): self {
        return $value instanceof self ? $value : self::from($value);
    }
    public function getHandler() : ModelHandler
    {
        return match ($this) {
            self::DEFAULT_MODEL => new ModelHandler("gpt-4.1-2025-04-14", provider: "open_ai"),
            self::DEFAULT_REASONING_MODEL => new ModelHandler("o3-2025-04-16", provider: "open_ai"),
            self::GPT_O3_CURRENT,
            self::GPT_O3,
            self::GPT_4O_2024_08_06,
            self::GPT_41_2025_04_14 => new ModelHandler($this->value, provider: "open_ai"),
        };
    }

}
