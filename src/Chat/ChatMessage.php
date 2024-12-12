<?php

namespace Lack\Kindergarden\Chat;

class ChatMessage
{


    public function __construct(
        public readonly ChatMessageRoleEnum $role,
        public string              $message)
    {

    }


    public function prepend(string $message): void
    {
        $this->message = $message . "\n" . $this->message;
    }

    public function append(string $message): void
    {
        $this->message = $this->message . "\n" . $message;
    }

}
