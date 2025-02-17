<?php

namespace Lack\Kindergarden\Cli;

use Lack\Kindergarden\Cli\Exception\CliArgumentMissingException;
use Lack\Kindergarden\Cli\Exception\CliCommandNotFoundException;
use Lack\Kindergarden\Cli\Exception\CliException;
use Lack\Kindergarden\Cli\Exception\CliOptionMissingException;

class CliGroupNode {
    public string|null $name;
    public string $description;
  // [['name'=>...,'short'=>...,'value'=>bool,'desc'=>...],...]
    public array $subNodes = [];
    public bool $interactive = false;
    public ?\Closure $handler = null;
    public CliApplication $app;

    public function __construct(CliApplication $app, string|null $name, string $description = '', )
    {
        $this->name = $name;
        $this->description = $description;
        $this->app = $app;
    }

    public function command(string $name, string $description = ''): CliActionNode
    {
        $cmd = new CliActionNode($this->app, $name, $description);
        if ($this->subNodes[$name] ?? false) {
            throw new \InvalidArgumentException("Command '{$name}' already exists.");
        }
        $this->subNodes[$name] = $cmd;
        return $cmd;
    }

    public function group(string $name, string $description = ''): CliGroupNode
    {
        $cmd = new CliGroupNode($this->app, $name, $description);

        if ($this->subNodes[$name] ?? false) {
            throw new \InvalidArgumentException("Command '{$name}' already exists.");
        }
        $this->subNodes[$name] = $cmd;
        return $cmd;
    }

    public function argument(string $name, string $description = '', bool $required = false): self
    {
        throw new \InvalidArgumentException('Cannot create an argument in a group node');
    }

    public function option(string $name, string $short = '', bool $hasValue = false, string $description = ''): self
    {

        throw new \InvalidArgumentException('Cannot create an option in a group node');
    }

    public function handler(callable $handler): self
    {
        throw new \InvalidArgumentException('Cannot set a handler in a group node');
    }

    public function setInteractive(bool $interactive = true): self
    {
        $this->interactive = $interactive;
        return $this;
    }

    public function run(array $args, array $path = [], bool $debug = false): void
    {

        // Subcommands
        if (!empty($this->subNodes) && isset($args[0]) && $args[0] !== '' && !str_starts_with($args[0], '-')) {
            $sub = $args[0];
            if (isset($this->subNodes[$sub])) {
                array_shift($args);
                $this->subNodes[$sub]->run($args, [...$path, $sub], $debug);
                return;
            } else {
                throw new CliCommandNotFoundException("Command '{$sub}' not found.");
            }
        }


        if (in_array('-h', $args, true) || in_array('--help', $args, true)) {
            $this->printHelp($path);
            exit(0);
        }

        $parsed = $this->parseArgs($args);

        // Check required arguments
        foreach ($this->arguments ?? [] as $i => $a) {
            if ($a['required'] && (!isset($parsed['args'][$i]) || $parsed['args'][$i] === '')) {
                if ($this->interactive) {
                    $parsed['args'][$i] = $this->prompt("Please provide a value for '{$a['name']}': ");
                } else {
                    throw new CliArgumentMissingException("Missing required argument '{$a['name']}'");
                }
            }
        }

        if (!$this->handler && empty($this->subNodes)) {
            throw new CliException("No handler defined for command '{$this->name}'.");
        }

        if ($debug) {
            $this->debugLog("Running command: " . implode(' ', [...$path ?: [$this->name]]));
            $this->debugLog("Arguments: " . json_encode($parsed['args']));
            $this->debugLog("Options: " . json_encode($parsed['opts']));
        }

        if ($this->handler) {
            call_user_func($this->handler, $parsed['args'], $parsed['opts']);
        } else {
            $this->printHelp($path);
            exit(1);
        }
    }

    private function parseArgs(array $args): array
    {

        $parsed = ['args' => [], 'opts' => []];
        $optsDefs = $this->options ?? [];

        while ($args) {
            $current = array_shift($args);
            if (str_starts_with($current, '--')) {
                $optName = substr($current, 2);
                $def = $this->findOpt($optName, $optsDefs);
                if (!$def) {
                    throw new CliOptionMissingException("Unknown option '--{$optName}'");
                }
                if ($def['value']) {
                    if (!$args) {
                        throw new CliOptionMissingException("Option '--{$optName}' requires a value.");
                    }
                    $parsed['opts'][$optName] = array_shift($args);
                } else {
                    $parsed['opts'][$optName] = true;
                }
            } elseif (str_starts_with($current, '-')) {
                $short = substr($current, 1);
                $def = $this->findOptShort($short, $optsDefs);
                if (!$def) {
                    throw new CliOptionMissingException("Unknown option '-{$short}'");
                }
                $optName = $def['name'];
                if ($def['value']) {
                    if (!$args) {
                        throw new CliOptionMissingException("Option '-{$short}' requires a value.");
                    }
                    $parsed['opts'][$optName] = array_shift($args);
                } else {
                    $parsed['opts'][$optName] = true;
                }
            } else {
                $parsed['args'][] = $current;
            }
        }

        return $parsed;
    }

    private function findOpt(string $name, array $opts)
    {
        foreach ($opts as $o) if ($o['name'] === $name) return $o;
        return null;
    }

    private function findOptShort(string $short, array $opts)
    {
        foreach ($opts as $o) if ($o['short'] === $short) return $o;
        return null;
    }

    public function printHelp(array $path): void
    {

        $full = $this->app->scriptName.' '. implode(' ', $path ?: [$this->name]);
        if ($this instanceof CliGroupNode)
            $full .= ' <command>';

        echo "Usage: {$full}";
        foreach ($this->options ?? [] as $o) {
            $flags = $o['short'] ? "-{$o['short']}|--{$o['name']}" : "--{$o['name']}";
            $flags =  "{$flags}" . ($o['value'] ? " <value>" : "") . "";
            if ($o["required"]) {
                $flags = "{$flags}";
            } else {
                $flags = "[{$flags}]";
            }
            echo " " . $flags;
        }
        foreach ($this->arguments ?? [] as $a) {
            echo $a['required'] ? " <{$a['name']}>" : " [{$a['name']}]";
        }
        echo "\n\n";

        if ($this->description) {
            echo "{$this->description}\n\n";
        }

        if ( !empty($this->subNodes) ) {
            echo "Commands:\n";
            $maxLength = max(array_map('strlen', array_keys($this->subNodes)));
            foreach ($this->subNodes as $name => $cmd) {
                $paddedName = str_pad($name, $maxLength);
                echo "  $paddedName  {$cmd->description}\n";
            }
            echo "\n";
        }


       if (!empty($this->arguments)) {
            echo "Arguments:\n";
            $maxArgLength = max(array_map(fn($a) => strlen($a['name']), $this->arguments));
            foreach ($this->arguments as $a) {
                $paddedName = str_pad($a['name'], $maxArgLength);
                echo "  {$paddedName} : {$a['desc']} ".($a['required'] ? '(required)' : '')."\n";
            }
            echo "\n";
       }

       if (!empty($this->options)) {
            echo "Options:\n";
            $maxLength = max(array_map(fn($o) => strlen($o['short'] ? "-{$o['short']}, --{$o['name']}" : "--{$o['name']}"), $this->options)) + 16;
            foreach ($this->options as $o) {
                $flags = $o['short'] ? "-{$o['short']}, --{$o['name']}" : "    --{$o['name']}";
                $flags .= $o['value'] ? " <value>" : "";
                $paddedFlags = str_pad($flags, $maxLength);
                echo "  {$paddedFlags} : {$o['desc']}" . ( $o['required'] ? " (required)" : "") . "\n";
            }
            echo "\n";
       }
    }

    public function printCompletion(array $path): void
    {
        file_put_contents('php://stderr', print_R($path, true));
        $curPath = array_shift($path);
        if (count ($path) > 0) {
            if (isset($this->subNodes[$curPath])) {
                $this->subNodes[$curPath]->printCompletion($path);
            } else {
                echo "";
            }
        } else {
            $subNodes = array_keys($this->subNodes);
            // Filter only commands starting with $curPath
            $subNodes = array_filter($subNodes, fn($v) => str_starts_with($v, $curPath));
            echo implode('\n', $subNodes);
        }
    }

    private function prompt(string $message): string
    {
        echo $message;
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        return trim($line);
    }

    private function debugLog(string $msg): void
    {
        echo "\033[33m[DEBUG]\033[0m $msg\n";
    }
}
