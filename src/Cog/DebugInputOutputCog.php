<?php

namespace Lack\Kindergarden\Cog;

use Lack\Kindergarden\Chat\Chat;
use Lack\Kindergarden\Cli\MultiLineCliOutput;
use Lack\Kindergarden\Cog\Type\EndOfStream;
use Lack\Kindergarden\Cog\Type\StartOfStream;
use Lack\Kindergarden\Driver\OpenAi\OpenAiRequest;

class DebugInputOutputCog extends AbstractCog
{
    // New property to toggle overwrite mode
    private bool $overwriteMode;

    public function __construct(bool $overwriteMode = true)
    {
        $this->overwriteMode = $overwriteMode;
    }

    private function debug_out(string $chunk, string $prefixText, int $maxchars = 150): void
    {
        static $buffer = '';
        static $curChars = 0;

        static $mulitLineCliOutput = new MultiLineCliOutput(3);
        // Append incoming data to the buffer
        $buffer .= $chunk;


        // Handle overwrite mode
        if ($this->overwriteMode) {
            // Clear the console line and overwrite
            if (strpos($buffer, "\n") === false)
                return;


            $mulitLineCliOutput->addLine("{$prefixText}" . str_replace("\n", "",$buffer));
            $buffer = "";
            return;
        }

        // Process the buffer line by line
        while (($pos = strpos($buffer, "\n")) !== false) {
            $line = substr($buffer, 0, $pos);
            $buffer = substr($buffer, $pos + 1);

            // Check maxchar and wrap line if necessary
            if ($curChars + strlen($line) > $maxchars && $curChars > 0) {
                echo "\n{$prefixText}";
                $curChars = 0;
            } elseif ($curChars === 0) {
                echo $prefixText;
            }

            echo $line . "\n";
            $curChars = 0; // Reset line length for the new line
        }

        // Handle remaining content in the buffer without newline
        if (strlen($buffer) > 0) {
            if ($curChars + strlen($buffer) > $maxchars && $curChars > 0) {
                echo "\n{$prefixText}";
                $curChars = 0;
            } elseif ($curChars === 0) {
                echo $prefixText;
            }

            echo $buffer;
            $curChars += strlen($buffer);
            $buffer = '';
        }
    }

    public function processChunk(Chat $chat, OpenAiRequest $request, string|StartOfStream|EndOfStream $data, ?callable $next): mixed
    {
        if ($data instanceof StartOfStream) {
            $this->debug_out("Start of stream", "DEBUG: ", 80);
        } elseif ($data instanceof EndOfStream) {
            $this->debug_out("End of stream", "DEBUG: ", 80);
            $this->debug_out("", "DEBUG: ");
        } else {
            $this->debug_out($data, "DEBUG: ");
        }
        return $next($data);
    }
}
