<?php

namespace Lack\Kindergarden\Cog;

use Lack\Kindergarden\Chat\Chat;
use Lack\Kindergarden\Cog\Type\CogMetaData;
use Lack\Kindergarden\Cog\Type\EndOfStream;
use Lack\Kindergarden\Cog\Type\StartOfStream;
use Lack\Kindergarden\Driver\OpenAi\OpenAiRequest;

class CreateModifyFileCog extends AbstractCog
{

    public function __construct(
        private string $filename,
        private ?string $name = "original-file-content",
        private ?string $userPrompt = null
    ) {

    }


    public function getCogMetaData(): ?CogMetaData
    {
        if ( ! file_exists($this->filename)) {
            return new CogMetaData(
                systemPrompt: "Your job is to create a file named '{$this->filename}' based on the instructions given. Respond only with the full file content."
            );
        }

        return new CogMetaData(
            name: "original-file-content",
            instructions: "This is the original content of the file '{$this->filename}'.",
            data: file_get_contents($this->filename),

            systemPrompt: "Your job is to modify and return the original-file-content of file '{$this->filename}' based on the instructions given. Respond only with the full modified original-file-content content after modifications. Do not remove any content or alter the content in any way unless specified in the prompt! There is no limit on output length. So do not worry about the length of the output. DO NOT wrap the output in any quotes, tags, backticks etc. (e.g. ```javascript or ```)!",
            userPrompt: $this->userPrompt !== null ? "Return the full original-file-content byte by byte as it is, unless changes are explicitly stated below!\nYour job is to modify the original-file-content according to the following instructions:\n\n {$this->userPrompt}" : null
        );
    }

    private $data = "";

    public function processChunk(Chat $chat, OpenAiRequest $request, string|StartOfStream|EndOfStream $data, ?callable $next): mixed
    {
        if (is_string($data)) {
            $this->data .= $data;
        }

        $dirname = dirname($this->filename);
        if ( ! is_dir($dirname)) {
            mkdir($dirname, 0777, true);
        }

        if ($data instanceof EndOfStream) {
            if (is_file($this->filename)) {
                rename($this->filename, $this->filename . ".bak");
            }

            // Check if file starts with ``` and ends with ``` -> remove first and last line (complete line)
            if (preg_match('/^```/', $this->data) && preg_match('/```$/', $this->data)) {
                $lines = explode("\n", $this->data);
                array_shift($lines);
                array_pop($lines);
                $this->data = implode("\n", $lines);
            }


            file_put_contents($this->filename, $this->data);
        }

        return $next($data);
    }


}
