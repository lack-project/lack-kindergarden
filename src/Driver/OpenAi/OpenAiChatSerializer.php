<?php

namespace Lack\Kindergarden\Driver\OpenAi;

use Lack\Kindergarden\Chat\Chat;
use Lack\Kindergarden\Chat\ChatMessageRoleEnum;
use Lack\Kindergarden\Chat\JsonChatResponseFormat;
use Lack\Kindergarden\Chat\PredictionChatResponseFormat;
use Lack\Kindergarden\ChatRequestDriver;
use Lack\Kindergarden\ChatSerializer;

class OpenAiChatSerializer implements  ChatSerializer
{

    public string $model;

    public float $temperature = 1.0;

    public int|null $maxTokens = null;

    public bool $stream = false;


    private function _mapRole(ChatMessageRoleEnum $role, bool $isReasoningModel): string
    {
        return match ($role) {

            ChatMessageRoleEnum::ASSISTANT => 'assistant',
            ChatMessageRoleEnum::SYSTEM => $isReasoningModel ? "user" : 'system',
            ChatMessageRoleEnum::USER => 'user',
        };
    }

    public function serialize(Chat $chat, ChatRequestDriver $chatRequestDriver): array
    {
        $isReasoningModel = str_starts_with($this->model, "o");

        if ($isReasoningModel) {
           // $this->stream = false; // Reasoning models do not support streaming
        }

        $data = [
            'model' => $this->model,
            'messages' => [],
            'stream' => $this->stream,
        ];

        if ($isReasoningModel) {
            $data["max_completion_tokens"] = $this->maxTokens;
        } else {
            if ($this->maxTokens !== null) {
                $data["max_tokens"] = $this->maxTokens;
            }
            $data["temperature"] = $this->temperature;
        }

        $responseFormat = $chat->getResponseFormat();
        if ($responseFormat instanceof JsonChatResponseFormat) {
            if ($responseFormat->jsonSchema) {
                $data["response_format"] = [
                    "type" => "json_schema",
                    "schema" => $responseFormat->jsonSchema
                ];
            } else {
                $data["response_format"] = [
                    "type" => "json_object"
                ];
            }
        }

        if ($responseFormat instanceof PredictionChatResponseFormat) {
            $data["prediction"] = [
                "type" => "content",
                "content" => $responseFormat->content
            ];
        }

        foreach ($chat->getMessages() as $currentMessage) {
            $data["messages"][] = [
                "role" => $this->_mapRole($currentMessage->role, $isReasoningModel),
                "content" => $currentMessage->message
            ];
        }
        return $data;
    }
}
