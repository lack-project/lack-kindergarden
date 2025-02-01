<?php

namespace Lack\Kindergarden\Cog;

use Lack\Kindergarden\Chat\Chat;
use Lack\Kindergarden\Cog\Type\EndOfStream;
use Lack\Kindergarden\Cog\Type\StartOfStream;
use Lack\Kindergarden\Driver\OpenAi\OpenAiRequest;

class DebugInputOutputCog extends AbstractCog
{
    private function debug_out(string $chunk, string $prefixText, int $maxchars = 150): void
    {
        static $buffer = '';
        static $curChars = 0;

        // Ankommende Daten anhängen
        $buffer .= $chunk;

        // Zeilenweise aus dem Puffer lesen
        while (($pos = strpos($buffer, "\n")) !== false) {
            $line = substr($buffer, 0, $pos);
            $buffer = substr($buffer, $pos + 1);

            // Maxchar prüfen und ggf. Zeile umbrechen
            if ($curChars + strlen($line) > $maxchars && $curChars > 0) {
                echo "\n{$prefixText}";
                $curChars = 0;
            } elseif ($curChars === 0) {
                echo $prefixText;
            }

            echo $line . "\n";
            $curChars = 0; // Neue Zeile -> Zeilenlänge zurücksetzen
        }

        // Verbleibender Teil ohne Newline
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
