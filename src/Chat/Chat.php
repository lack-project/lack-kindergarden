<?php

namespace Lack\Kindergarden\Chat;

class Chat
{

    public function __construct(
        /**
         * @var ChatMessage[]|ChatImageMessage[]
         */
        private array $messages = []
    )
    {

    }




    private ChatResponseFormat|null $responseFormat = null;


    public function addMessage(ChatMessage $message) : self
    {
        $this->messages[] = $message;
        return $this;
    }

    /**
     * @return ChatImageMessage[]|ChatMessage[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }


    public function setResponseFormat(ChatResponseFormat|null $responseFormat) : self
    {
        $this->responseFormat = $responseFormat;
        return $this;
    }

    public function getResponseFormat(): ChatResponseFormat|null
    {
        return $this->responseFormat;
    }


}
