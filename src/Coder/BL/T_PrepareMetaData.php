<?php

namespace Lack\Kindergarden\Coder\BL;

class T_PrepareMetaData
{

    /**
     * The name of the task in slug format (lowercase, no spaces, - as separator) should be usable as a filename (so short and descriptive)
     * @var string
     */
    public $slugName;

    /**
     * List of all files that should be included in the prompt
     *
     * @var string[]
     */
    public $inlcudeFiles = [];

    /**
     * Full path of each file that should be edited or created.
     *
     * @var string[]
     */
    public $editFiles = [];

    /**
     * Leave empty. Will be filled automatically.
     * @var string
     */
    public $original_prompt = "";

}
