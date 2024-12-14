<?php

namespace Lack\Kindergarden\Cli;

use Lack\Kindergarden\Cli\Attributes\CliArgument;
use Lack\Kindergarden\Cli\Attributes\CliCommand;
use Lack\Kindergarden\Cli\Attributes\CliParamDescription;
use Lack\Kindergarden\Cli\Console\ConsoleColor;
use Lack\Kindergarden\Cli\Console\Verbosity;
use Lack\Kindergarden\Cli\Exception\CliException;

class CliApplication {
    private static ?CliApplication $instance = null;
    private CliGroupNode $root;
    private bool $debug = false;

    public Console $console;

    public readonly string $scriptName;

    private function __construct(string $name = 'app', string $description = '')
    {
        $this->root = new CliGroupNode($this, null, $description);
        $this->console = new Console();
    }

    public static function getInstance(string $name = 'app', string $description = ''): self
    {
        if (self::$instance === null) {
            self::$instance = new self($name, $description);
        }
        return self::$instance;
    }

    public function node(): CliGroupNode
    {
        return $this->root;
    }

    public function registerClass(string $class): void
    {
        if (method_exists($class, '__cli')) {
            $class::__cli($this->root);
        }


        $ref = new \ReflectionClass($class);
        $instance = $ref->newInstance();

        if (method_exists($instance, '__set_console')) {
            $instance->__set_console($this->console);
        }


        foreach ($ref->getMethods() as $method) {
            $cmdAttrs = $method->getAttributes(CliCommand::class);
            if (!$cmdAttrs) continue;

            /** @var CliCommand $cmdMeta */
            $cmdMeta = $cmdAttrs[0]->newInstance();
            $path = explode(':', $cmdMeta->name);
            $node = $this->root;




            // Load subnodes
            foreach (array_slice($path, 0, -1) as $p) {
                if (!isset($node->subNodes[$p])) {
                    throw new \InvalidArgumentException("Node '{$p}' not found in path '" . implode(':', $path) . "' - make sure to create the group node first");
                }
                $node = $node->subNodes[$p];
            }

            $last = end($path);
            $cmdNode = new CliActionNode($this, $last, $cmdMeta->description);

            // Parse Arguments
            $attrAttrs = $method->getAttributes(CliArgument::class);
            $attrAttrs = array_map(fn($a) => $a->newInstance(), $attrAttrs);
            foreach ($attrAttrs as $attr) {
                $cmdNode->argument($attr->name, $attr->description, $attr->required, $attr->multiple);
            }
            // Automatically determine args/options by method signature
            $params = $method->getParameters();
            foreach ($params as $p) {
                // Check if param is $args or $opts
                if ($p->getName() === 'argv' || $p->getName() === 'opts') {
                    // We'll pass full arrays later, no definition needed
                    continue;
                }

                $paramDesc = '';
                // Look for CliParamDescription attributes on parameter
                $pAttrs = $p->getAttributes(CliParamDescription::class);
                if ($pAttrs) {
                    $paramDescObj = $pAttrs[0]->newInstance();
                    $paramDesc = $paramDescObj->description;
                }

                $type = $p->getType();
                $typeName = $type ? $type->getName() : 'string';
                $hasDefault = $p->isDefaultValueAvailable();
                $isOptional = $hasDefault || ($type && $type->allowsNull());

                // If bool -> option
                $optName = $this->paramToOptionName($p->getName());
                $cmdNode->option($optName, '', $typeName !== "bool", $paramDesc, !$isOptional);

            }

            $cmdNode->handler(function($cliArgs, $cliOpts) use ($instance, $method) {
                $params = $method->getParameters();
                $finalArgs = [];
                $argIndex = 0; // for positional args
                foreach ($params as $p) {
                    $type = $p->getType();
                    $typeName = $type ? $type->getName() : 'string';
                    $paramName = $p->getName();

                    if ($paramName === 'argv') {
                        $finalArgs[] = $cliArgs;
                        continue;
                    }
                    if ($paramName === 'opts') {
                        $finalArgs[] = $cliOpts;
                        continue;
                    }

                    // Boolean option
                    $optName = $this->paramToOptionName($paramName);

                    // argument
                    if (isset($cliOpts[$optName])) {
                        $finalArgs[] = $cliOpts[$optName];
                    } else {
                        // If optional and no arg given
                        if ($p->isDefaultValueAvailable()) {
                            $finalArgs[] = $p->getDefaultValue();
                        } else {
                            $finalArgs[] = null;
                        }
                    }

                }
                return $method->invokeArgs($instance, $finalArgs);
            });

            $node->subNodes[$last] = $cmdNode;
        }
    }

    public function run(array $argv = null): void
    {
        $argv = $argv ?? $_SERVER['argv'];

        $this->scriptName = array_shift($argv); // Remove script name

        if (empty($argv) || $argv[0] === '-h' || $argv[0] === '--help') {
            $this->printGobalHelp();
            $this->root->printHelp([]);
            exit(1);
        }

        try {
            $globalParsed = $this->parseGlobalOptions($argv);
            $this->debug = !empty($globalParsed['opts']['debug']);
            $interactive = !empty($globalParsed['opts']['interactive']);
            $completion = !empty($globalParsed['opts']['complete']);
            $verbosity = $globalParsed['opts']['verbosity'] ?? Verbosity::INFO;

            if ($interactive) {
                $this->setInteractive($this->root);
            }

            if ($completion) {
                $this->printCompletion($this->root, []);
                exit(0);
            }
            $this->console->setVerbosity($verbosity);

            $this->root->run($argv, [], $this->debug);
        } catch (CliException $e) {

            $this->console->renderException($e);
            exit(1);
        }
    }

    private function parseGlobalOptions(array &$argv): array
    {
        $opts = [];
        $stop = false;

        while ($argv && !$stop) {
            $current = $argv[0];

            if ($current === '--debug' || $current === '-d') {
                $opts['debug'] = true;
                array_shift($argv);
            } elseif ($current === '--interactive' || $current === '-i') {
                $opts['interactive'] = true;
                array_shift($argv);
            } elseif ($current === '--complete') {
                $opts['complete'] = true;
                array_shift($argv);
            } elseif (str_starts_with($current, '-v')) {
                $verbosity = 3;
                if (strlen($current) == 3) {
                    $verbosity = (int) substr($current, 2);
                }
                $opts['verbosity'] = Verbosity::from($verbosity);
                array_shift($argv);
            } else {
                $stop = true;
            }
        }

        return ['opts' => $opts];
    }


    private function printGobalHelp(): void
    {
        $this->console->writeln("Usage: {$this->scriptName} [options] <command> [arguments]\n", ConsoleColor::BLUE, true);
        echo "Options:\n";
        echo "   -v0-5              Set verbosity level\n";
        echo "  --debug, -d         Enable debug mode\n";
        echo "  --interactive, -i      Enable interactive mode\n";
        echo "  --complete          Print completion suggestions\n";
        echo "\n";
    }



    private function setInteractive(CliGroupNode $node): void
    {
        $node->setInteractive(true);
        foreach ($node->subNodes as $sub) {
            $this->setInteractive($sub);
        }
    }

    private function printCompletion(CliGroupNode $node, array $path): void
    {
        echo implode(':', array_filter([...$path, $node->name])) . "\n";
        foreach ($node->subNodes as $sub) {
            $this->printCompletion($sub, [...$path, $node->name]);
        }
    }

    private function paramToOptionName(string $paramName): string
    {
        return strtolower(str_replace('_','-',$paramName));
    }
}
