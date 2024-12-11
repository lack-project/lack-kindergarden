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

    /**
     * Get the first system message (first message). If not existing will create a empty one
     *
     * @return ChatMessage
     */
    public function getFirstSystemMessage() : ChatMessage
    {
        foreach ($this->messages as $message) {
            if ($message instanceof ChatMessage && $message->role === ChatMessageRoleEnum::SYSTEM) {
                return $message;
            }

        }
        // Create a empty Message
        $message = new ChatMessage(ChatMessageRoleEnum::SYSTEM, "");
        // Insert it on top of the messages
        array_unshift($this->messages, $message);
        return $message;
    }

    public function getFirstUserMessage() : ChatMessage
    {
        foreach ($this->messages as $message) {
            if ($message instanceof ChatMessage && $message->role === ChatMessageRoleEnum::USER) {
                return $message;
            }

        }
        // Create a empty Message
        $message = new ChatMessage(ChatMessageRoleEnum::USER, "");
        // Insert it on top of the messages
        array_unshift($this->messages, $message);
        return $message;
    }


}
