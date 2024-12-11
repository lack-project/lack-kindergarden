<?php

namespace Lack\Kindergarden\Cog\Type;

class T_InputFile
{

    public function __construct (

        /**
         * The filename of the file
         */
        public string $filename,

        /**
         * The text/plain content of the file as it is
         *
         * If it is null, assume that the file exists in the filesystem but the content is not provided.
         *
         * @var string|null
         */
        public string|null $content,


        /**
         * Additional instructions for interpreting the file (if provided)
         *
         * @var string|null
         */
        public string|null $instructions

    ) {}



}
