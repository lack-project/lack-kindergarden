<?php

namespace Lack\Kindergarden\CogToChatSerializer;

use Lack\Kindergarden\Chat\Chat;
use Lack\Kindergarden\Chat\ChatMessage;
use Lack\Kindergarden\Chat\ChatMessageRoleEnum;
use Lack\Kindergarden\Cog;

class JsonCogToChatSerializer
{

    /**
     * @param Cog[] $cogs
     * @return Chat
     * @throws \Exception
     */
    public function convert(array $cogs) : Chat
    {
        $chat = new Chat();
        $userMessageData = [];
        $responseFormat = null;
        foreach ($cogs as $inputCog) {
            if ( !($inputCog instanceof Cog) )
                throw new \Exception("CogWerk can only contain Cog instances");
            $curMeta = $inputCog->getCogMetaData();
            if ($curMeta === null)
                continue;
            if ($curMeta->getCogInputStruct() !== null) {
                $userMessageData[] = $curMeta->getCogInputStruct();
            }
            if ($curMeta->responseFormat !== null) {
                if ($responseFormat !== null)
                    throw new \Exception("Multiple response formats not supported");
                $responseFormat = $curMeta->responseFormat;
            }
            if ($curMeta->systemPrompt !== null) {
                $chat->getFirstSystemMessage()->append($curMeta->systemPrompt);
            }
            if ($curMeta->userPrompt !== null) {
                $chat->getFirstUserMessage()->append($curMeta->userPrompt);
            }
        }

        $chat->addMessage(new ChatMessage(ChatMessageRoleEnum::USER, json_encode($userMessageData)));

        return $chat;

    }

}
