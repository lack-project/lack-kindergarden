<?php

namespace Lack\Kindergarden\Chat;

class ChatMessage
{


    public function __construct(
        public readonly ChatMessageRoleEnum $role,
        public string              $message)
    {

    }


}
