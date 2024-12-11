<?php

namespace Lack\Kindergarden\Driver\OpenAi;

use Lack\Kindergarden\Chat\Chat;
use Lack\Kindergarden\Chat\ChatMessageRoleEnum;
use Lack\Kindergarden\Chat\JsonChatResponseFormat;
use Lack\Kindergarden\ChatRequestDriver;
use Lack\Kindergarden\ChatSerializer;

class OpenAiChatSerializer implements  ChatSerializer
{

    public string $model;

    public float $temperature = 1.0;

    public int $maxTokens = 150;

    public bool $stream = false;


    private function _mapRole(ChatMessageRoleEnum $role): string
    {
        return match ($role) {

            ChatMessageRoleEnum::ASSISTANT => 'assistant',
            ChatMessageRoleEnum::SYSTEM => 'system',
            ChatMessageRoleEnum::USER => 'user',
        };
    }

    public function serialize(Chat $chat, ChatRequestDriver $chatRequestDriver): array
    {
        $data = [
            'model' => $this->model,
            'messages' => [],
            'max_tokens' => $this->maxTokens,
            'temperature' => $this->temperature,
            'stream' => $this->stream,
        ];
        $responseFormat = $chat->getResponseFormat();
        if ($responseFormat instanceof JsonChatResponseFormat) {
            if ($responseFormat->jsonSchema) {
                $data["response_format"] = [
                    "type" => "json_schema",
                    "schema" => $responseFormat->jsonSchema
                ];
            } else {
                $data["response_format"] = [
                    "type" => "json"
                ];
            }
        }

        foreach ($chat->getMessages() as $currentMessage) {
            $data["messages"][] = [
                "role" => $this->_mapRole($currentMessage->role),
                "content" => $currentMessage->message
            ];
        }
        return $data;
    }
}
