<?php

namespace Lack\Test\Kindergarden\OpenAi;

use Lack\Keystore\KeyStore;
use Lack\Kindergarden\Chat\Chat;
use Lack\Kindergarden\Chat\ChatMessage;
use Lack\Kindergarden\Chat\ChatMessageRoleEnum;
use Lack\Kindergarden\Driver\OpenAi\OpenAiClient;
use Lack\Kindergarden\Driver\OpenAi\OpenAiRequest;

class OpenAiClientTest extends \PHPUnit\Framework\TestCase
{


    public function testBaseRequest() {
        $request = new OpenAiRequest(KeyStore::Get()->getAccessKey('open_ai'), "gpt-4o");

        $chat = new Chat();
        $chat->addMessage(new ChatMessage(ChatMessageRoleEnum::SYSTEM, 'Say Hello'));

        $request->setChat($chat);
        $request->execute();
    }

}
