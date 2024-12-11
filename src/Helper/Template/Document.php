<?php

namespace Lack\Kindergarden\Helper\Template;

class Document
{

    public function __construct(public readonly array $metaData, public readonly string $content)
    {

    }

}
