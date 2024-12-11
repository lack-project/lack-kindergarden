<?php

namespace Lack\Kindergarden\Chat;

class Chat
{

    /**
     * @var array ChatMessage[]|ChatImageMessage[]
     */
    private $mesages = [];
    public function addMessage(ChatMessage $message)
    {
        $this->mesages[] = $message;
    }

}
