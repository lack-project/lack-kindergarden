<?php

namespace Lack\Kindergarden\Cli;

use Lack\Kindergarden\Cli\Console\ConsoleColor;
use Lack\Kindergarden\Cli\Console\ConsoleStyle;
use Lack\Kindergarden\Cli\Console\Verbosity;

final class Console
{
    private bool $interactive;
    private bool $colorEnabled;
    private Verbosity $verbosity;

    public function __construct(bool $interactive = true, bool $colorEnabled = true, Verbosity $verbosity = Verbosity::DEBUG)
    {
        $this->interactive = $interactive;
        $this->colorEnabled = $colorEnabled;
        $this->verbosity = $verbosity;
    }

    public function setVerbosity(Verbosity $verbosity): void
    {
        $this->verbosity = $verbosity;
    }

    public function write(string $text, ?ConsoleColor $color = null, bool $bold = false): void
    {
        if ($this->verbosity === Verbosity::NONE) {
            return;
        }

        $styledText = $this->colorEnabled ? $this->applyStyle($text, $color, $bold) : $text;
        echo $styledText;
    }

    public function writeln(string $text, ?ConsoleColor $color = null, bool $bold = false): void
    {
        $this->write($text . PHP_EOL, $color, $bold);
    }

    public function ask(string $prompt, string $default = ''): string
    {
        if (!$this->interactive) {
            return $default;
        }
        $this->writeln($prompt, ConsoleColor::BLUE, false);
        $answer = trim(fgets(STDIN));
        return $answer === '' ? $default : $answer;
    }

    public function confirm(string $prompt, bool $default = true): bool
    {
        if (!$this->interactive) {
            return $default;
        }
        $defaultText = $default ? '[Y/n]' : '[y/N]';
        $this->write($prompt . ' ' . $defaultText . ' ', ConsoleColor::BLUE);
        $answer = strtolower(trim(fgets(STDIN)));
        if ($answer === '') {
            return $default;
        }
        return in_array($answer, ['y', 'yes'], true);
    }

    public function select(string $prompt, array $options, string $default = ''): string
    {
        if (!$this->interactive) {
            return $default;
        }
        $this->writeln($prompt, ConsoleColor::BLUE);
        foreach ($options as $i => $opt) {
            $this->writeln("  [$i] $opt");
        }
        $chosen = trim(fgets(STDIN));
        return array_key_exists($chosen, $options) ? $options[$chosen] : $default;
    }

    public function clear(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            system('cls');
        } else {
            system('clear');
        }
    }

    public function log(string $message): void
    {
        if ((int)$this->verbosity->value >= (int)Verbosity::DEBUG->value) {
            $this->writeln($message, ConsoleColor::DEFAULT);
        }
    }

    public function info(string $message): void
    {
        if ((int)$this->verbosity->value >= (int)Verbosity::INFO->value) {
            $this->writeln($message, ConsoleColor::BLUE);
        }
    }

    public function success(string $message): void
    {
        if ((int)$this->verbosity->value >= (int)Verbosity::INFO->value) {
            $this->writeln($message, ConsoleColor::GREEN, true);
        }
    }

    public function error(string $message): void
    {
        if ((int)$this->verbosity->value >= (int)Verbosity::ERROR->value) {
            $this->writeln($message, ConsoleColor::RED, true);
        }
    }

    public function warn(string $message): void
    {
        if ($this->verbosity->value >= Verbosity::WARN->value) {
            $this->writeln($message, ConsoleColor::YELLOW, true);
        }
    }

    public function renderException(\Throwable $e): void
    {
        if ($this->verbosity === Verbosity::NONE) {
            return;
        }

        $this->error('Exception: ' . $e->getMessage());
        if ((int)$this->verbosity->value >= (int)Verbosity::DEBUG->value) {
            $this->warn("Thrown in file: " . $e->getFile() . " (Line " . $e->getLine() . ")");
            $this->warn("Trace:");
            foreach (explode(PHP_EOL, $e->getTraceAsString()) as $trace) {
                $this->writeln(print_r($trace, true), ConsoleColor::YELLOW);
            }
        }
    }

    public function highlight(string $text, ?ConsoleColor $bgColor = null): void
    {
        $width = $this->getConsoleWidth();
        $boxWidth = max(mb_strlen($text) + 4, 20);
        $pad = ($boxWidth - mb_strlen($text) - 2) / 2;
        $line = str_repeat(' ', floor($pad)) . $text . str_repeat(' ', ceil($pad));
        $topBottom = str_repeat('─', $boxWidth - 2);

        $this->writeln('┌' . $topBottom . '┐', $bgColor, true);
        $this->writeln('│' . $line . '│', $bgColor, true);
        $this->writeln('└' . $topBottom . '┘', $bgColor, true);
    }

    public function progress(string $message): void
    {
        if ((int)$this->verbosity === (int)Verbosity::NONE) {
            return;
        }
        static $state = 0;
        $states = ['⠁','⠂','⠄','⡀','⢀','⠠','⠐','⠈'];
        $state = ($state + 1) % count($states);
        $this->write("\r" . $states[$state] . ' ' . $message);
        fflush(STDOUT);
    }

    private function applyStyle(string $text, ?ConsoleColor $color, bool $bold): string
    {
        $colorCode = $color?->value ?? ConsoleColor::DEFAULT->value;
        $styleCode = $bold ? ConsoleStyle::BOLD->value : ConsoleStyle::NORMAL->value;
        return "\033[".$styleCode.";".$colorCode."m".$this->parseMarkdown($text)."\033[0m";
    }

    private function parseMarkdown(string $text): string
    {
        return preg_replace('/\*\*(.*?)\*\*/', "\033[" . ConsoleStyle::BOLD->value . "m$1\033[0m", $text);
    }

    private function getConsoleWidth(): int
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return 80; // vereinfacht
        } else {
            $output = [];
            exec('tput cols', $output);
            return (int)$output[0] ?: 80;
        }
    }
}
