<?php

namespace Lack\Kindergarden;

use Lack\Kindergarden\Chat\Chat;

interface ChatSerializer
{

    public function serialize(Chat $chat, ChatRequestDriver $chatRequestDriver): array;

}
