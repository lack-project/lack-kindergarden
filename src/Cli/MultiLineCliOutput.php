<?php

namespace Lack\Kindergarden\Cli;

class MultiLineCliOutput
{

    private int $maxLines;
    private array $lines = [];
    private int $width;
    private string $bgColor;
    private string $fgColor;

    public function __construct(int $maxLines, string $bgColor = "47", string $fgColor = "37")
    {
        $this->maxLines = $maxLines;
        $this->bgColor = $bgColor;
        $this->fgColor = $fgColor;
        $this->width = (int) exec('tput cols') ?: 80; // Get terminal width

        // Fill initial lines with empty strings
        $this->lines = array_fill(0, $maxLines, str_repeat(' ', $this->width));
    }

    public function addLine(string $line): void
    {
        // Allow empty lines and ensure they fit within terminal width
        $line = str_pad(substr($line, 0, $this->width), $this->width, ' ');

        // Append line and keep only maxLines
        $this->lines[] = $line;
        if (count($this->lines) > $this->maxLines) {
            array_shift($this->lines);
        }

        // Move cursor up for rewriting
        echo "\033[" . ($this->maxLines) . "A";

        // Start background block
        echo "\033[" . $this->bgColor . "m";

        // Reprint debug box with full-width background
        foreach ($this->lines as $l) {
            echo "\033[2K\033[" . $this->fgColor . "m" . $l . "\n";
        }

        // End background block
        echo "\033[0m";

        // Move cursor down for normal output
        echo "\033[" . ($this->maxLines - count($this->lines)) . "B";
    }

    public function clear(): void
    {
        // Move cursor up
        echo "\033[" . count($this->lines) . "A";

        // Start background block
        echo "\033[" . $this->bgColor . "m";

        // Clear lines with full-width background
        foreach ($this->lines as $_) {
            echo "\033[2K" . str_repeat(' ', $this->width) . "\n";
        }

        // End background block
        echo "\033[0m";

        // Move cursor back up
        echo "\033[" . count($this->lines) . "A";

        // Reset
        $this->lines = array_fill(0, $this->maxLines, str_repeat(' ', $this->width));
    }
}
