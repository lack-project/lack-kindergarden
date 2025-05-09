<?php

namespace Lack\Kindergarden\Cog;

use Lack\Kindergarden\Chat\Chat;
use Lack\Kindergarden\Chat\ChatMessage;
use Lack\Kindergarden\Chat\ChatMessageRoleEnum;
use Lack\Kindergarden\Chat\PredictionChatResponseFormat;
use Lack\Kindergarden\Cog;
use Lack\Kindergarden\Cog\Type\EndOfStream;
use Lack\Kindergarden\Cog\Type\StartOfStream;
use Lack\Kindergarden\Driver\OpenAi\OpenAiRequest;
use Lack\Kindergarden\OutputCog;

class FileOutputCog extends AbstractCog
{


    public function __construct(
        public readonly string $rootPath,
        public readonly string $filename,
        public readonly bool $mustExist = true,
        public string|null $instructions = null)
    {
    }

    /*
    public function prepareChat(Chat $chat): void
    {
        $absolutePath = $this->rootPath . '/' . $this->filename;

        if ( ! file_exists($absolutePath)) {
            $chat->getFirstSystemMessage()->prepend("Your job is to create a file named '{$this->filename}' based on the instructions given. Respond only with the file content. Do not wrap output in quotes.");
        } else {
            $content = file_get_contents($absolutePath);
            if ($content === false) {
                throw new \Exception("Could not read file $absolutePath");
            }
            $chat->getFirstSystemMessage()->prepend("Your job is to rewrite the content of the file '{$this->filename}' provided based on the instructions given. Respond only with the modified file content. Do not wrap output in quotes.");
            $chat->getFirstUserMessage(); // Skip the first user message
            $chat->addMessage(new ChatMessage(ChatMessageRoleEnum::USER, $content)); // The original content
            $chat->setResponseFormat(new PredictionChatResponseFormat($content)); //
        }
    }
    */

    #[\Override]
    public function getCogMetaData(): ?Cog\Type\CogMetaData
    {
        $absolutePath = $this->rootPath . '/' . $this->filename;
        if ($this->mustExist && !file_exists($absolutePath)) {
            throw new \Exception("FileOutputCog File: '{$absolutePath}' does not exist (mustExist = true).");
        }
        if (file_exists($absolutePath) && !is_writable($absolutePath)) {
            throw new \Exception("FileOutputCog File: '{$absolutePath}' is not writable.");
        }
        $prompt = "Your job is to create a file named '{$this->filename}' based on the instructions given. Respond only with the file content. Do not wrap output in quotes.";
        if (file_exists($this->rootPath . '/' . $this->filename)) {
            $prompt = "Your job is to rewrite the content of the file '{$this->filename}' provided based on the instructions given. Respond only with the modified file content. Do not wrap output in quotes.";
        }

        return new Cog\Type\CogMetaData(
            systemPrompt: $prompt
        );
    }

    public function processChunk(Chat $chat, OpenAiRequest $request, string|StartOfStream|EndOfStream $data, ?callable $next): mixed
    {
        $absolutePath = $this->rootPath . '/' . $this->filename;
        mkdir(dirname($absolutePath), 0777, true);

        $result = file_put_contents($absolutePath, $data, );
        if ($result === false) {
            throw new \Exception("Could not write to file $absolutePath");
        }
        return $next($data);
    }


}
