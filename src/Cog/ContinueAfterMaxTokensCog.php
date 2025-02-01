<?php

namespace Lack\Kindergarden\Cog;

use Lack\Kindergarden\Chat\Chat;
use Lack\Kindergarden\Chat\ChatMessage;
use Lack\Kindergarden\Chat\ChatMessageRoleEnum;
use Lack\Kindergarden\Cog\Type\EndOfStream;
use Lack\Kindergarden\Cog\Type\StartOfStream;
use Lack\Kindergarden\Driver\OpenAi\OpenAiRequest;

class ContinueAfterMaxTokensCog extends AbstractCog
{

    private $currentRequest = 0;
    private $currentOutput = "";

    private $startOfStreamReceived = false;

    public function __construct(
        public int $maxTokensPerRequest = 100,
        public int $maxTurns = 4
    ) {
    }


    #[\Override]
    public function processChunk(Chat $chat, OpenAiRequest $request, string|StartOfStream|EndOfStream $data, ?callable $next): mixed
    {

        if (is_string($data)) {
            $this->currentOutput .= $data;
            return $next($data);
        }

        if ($data instanceof StartOfStream) {
            if ($this->startOfStreamReceived) {
                throw new \InvalidArgumentException("StartOfStream received twice");
            }
            $this->startOfStream = true;
            return $next($data);
        }

        if ($data instanceof EndOfStream) {
            if ( ! $data->completedNaturally && $this->currentRequest < $this->maxTurns) {
                $this->currentRequest++;
                $this->startOfStreamReceived = false;
                $request->getChat()->addMessage(new ChatMessage(ChatMessageRoleEnum::ASSISTANT, $this->currentOutput));
                $request->getChat()->addMessage(new ChatMessage(ChatMessageRoleEnum::USER, "You just ran into the max tokens output limit! You must continue the output exactly from the last character of the last assistant-message. Do not comment on the limit."));
                $this->currentOutput = "";
                $request->execute();
                $this->processChunk($chat, $request, new EndOfStream($request->completedNaturally()), $next);
                return null;
            }
        }

        return $next($data);
    }


}
