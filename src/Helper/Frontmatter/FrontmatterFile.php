<?php

namespace Lack\Kindergarden\Helper\Frontmatter;

class FrontmatterFile
{
    private array $header = [];
    private string $content = '';
    private ?string $filePath = null;
    private ?\Closure $postProcessor = null;

    public function __construct(?string $filePath = null)
    {
        if ($filePath) {
            $this->load($filePath);
        }
    }

    public function load(string $filePath): void
    {
        if (!is_readable($filePath)) {
            throw new FrontmatterException("File not readable: $filePath");
        }
        $raw = file_get_contents($filePath);
        if ($raw === false) {
            throw new FrontmatterException("Could not read file: $filePath");
        }

        $this->filePath = $filePath;
        if (trim($raw) === '') {
            $this->header = [];
            $this->content = '';
            return;
        }
        $parts = preg_split('/^-{3}\s*$/m', $raw, 3);
        if (count($parts) < 3) {
            throw new FrontmatterException("Invalid format");
        }

        $this->header = $this->parseYaml($parts[1]);
        $this->content = ltrim($parts[2]);
    }

    public function create(array $header, string $content = ''): void
    {
        $this->header = $header;
        $this->content = $content;
        $this->filePath = null;
    }

    public function setHeader(array $header): void
    {
        $this->header = $header;
    }

    public function getHeader(): array
    {
        return $this->header;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function setPostProcessor(callable $callback): void
    {
        $this->postProcessor = $callback;
    }

    public function save(?string $filePath = null): void
    {
        $targetPath = $filePath ?? $this->filePath;
        if (!$targetPath) {
            throw new FrontmatterException("No target file path.");
        }
        if ($this->postProcessor) {
            $this->header = call_user_func($this->postProcessor, $this->header, $targetPath);
            if (!is_array($this->header)) {
                throw new FrontmatterException("PostProcessor must return array.");
            }
        }
        $frontmatter = "---\n" . $this->generateYaml($this->header) . "---\n" . $this->content;
        if (file_put_contents($targetPath, $frontmatter) === false) {
            throw new FrontmatterException("Write error: $targetPath");
        }
        $this->filePath = $targetPath;
    }

    private function parseYaml(string $yaml): array
    {
        if ( ! function_exists('yaml_parse')) {
            throw new FrontmatterException("YAML extension not available.");
        }
        $parsed = yaml_parse($yaml);
        if (!is_array($parsed)) {
            throw new FrontmatterException("YAML parse error");
        }
        return $parsed;
    }

    private function generateYaml(array $data): string
    {
        if ( ! function_exists('yaml_emit')) {
            throw new FrontmatterException("YAML extension not available.");
        }
        return yaml_emit($data);
    }
}
