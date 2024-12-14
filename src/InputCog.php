<?php

namespace Lack\Kindergarden;

use Lack\Kindergarden\Chat\Chat;

interface InputCog
{
    public function prepareChat(Chat $chat): void;

    public function getData(): array;
}
