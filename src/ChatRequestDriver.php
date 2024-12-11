<?php

namespace Lack\Kindergarden;

use Lack\Kindergarden\Chat\Chat;

interface ChatRequestDriver
{

    public function setChat(Chat $chat): ChatRequestDriver;

}
