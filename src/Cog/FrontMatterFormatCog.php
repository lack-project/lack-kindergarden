<?php

namespace Lack\Kindergarden\Cog;

use Lack\Kindergarden\Chat\Chat;
use Lack\Kindergarden\Cog;
use Lack\Kindergarden\Cog\Type\EndOfStream;
use Lack\Kindergarden\Cog\Type\StartOfStream;
use Lack\Kindergarden\Driver\OpenAi\OpenAiRequest;
use Lack\Kindergarden\Helper\Frontmatter\FrontmatterException;
use Lack\Kindergarden\Helper\JsonSchemaGenerator;

/**
 * @template T
 */
class FrontMatterFormatCog extends AbstractCog
{

    private $schema = null;


    /**
     * @param class-string<T>|null $metaDataClass
     */
    public function __construct(private ?string $metaDataClass = null)
    {
        if ($metaDataClass) {
            $this->schema = JsonSchemaGenerator::buildSchema($metaDataClass);
        }
    }


    public function getCogMetaData(): ?Cog\Type\CogMetaData
    {


        return new Cog\Type\CogMetaData(
            systemPrompt: "The output must be YAML frontmatter formatted. Start the first output with --- then the yaml data then --- then the markdown formatted content. Do not encapsulate the yaml nor the markdown in any tags. " . ($this->schema ? " The YAML data must match the json-schema: " . json_encode($this->schema) . " Consult the description of the json-schema for details on how to generate the data." : ""),
        );
    }

    private $data = "";

    public function processChunk(Chat $chat, OpenAiRequest $request, string|StartOfStream|EndOfStream $data, ?callable $next): mixed
    {
        if (is_string($data)) {
            $this->data .= $data;
        }
        return $next($data);
    }



    private $header = null;
    private $content = null;


    private function parse() {
        if ($this->content !== null) {
            return;
        }
        $raw = $this->data;
        if (trim($raw) === '') {
            $this->header = null;
            $this->content = '';
            return;
        }
        $parts = preg_split('/^-{3}\s*$/m', $raw, 3);
        if (count($parts) < 3) {
            throw new FrontmatterException("Invalid frontmatter format");
        }

        $this->header = $parts[1];
        if ($this->metaDataClass !== null)
            $this->header = phore_hydrate($this->parseYaml($parts[1]), $this->metaDataClass);
        $this->content = ltrim($parts[2]);
    }

    private function parseYaml(string $yaml): array
    {
        if ( ! function_exists('yaml_parse')) {
            throw new FrontmatterException("YAML extension not available.");
        }
        $parsed = phore_yaml_decode($yaml);

        if (!is_array($parsed)) {
            throw new FrontmatterException("YAML parse error");
        }
        return $parsed;
    }



    /**
     * @return T
     */
    public function getHeader()
    {
        $this->parse();
        return $this->header;
    }


    public function getContent(): string
    {
        $this->parse();
        return $this->content;
    }

    public function __toString(): string
    {
        $this->parse();
        return "---\n" . rtrim (phore_yaml_encode(phore_object_to_array($this->header ?? []))) . "\n---\n" . $this->content;
    }

}
