<?php

namespace Lack\Kindergarden\Helper\Template;

class MultipartTemplate
{

    public function __construct(
        private ?string $templateString = null,
        private ?string $templateFile = null,
        private ?TemplateRenderer $renderer = null
    )
    {
        if ($this->renderer === null) {
            $this->renderer = new TemplateRenderer();
        }
        if ($this->templateString === null && $this->templateFile === null) {
            throw new \Exception("You must provide a template string or a template file");
        }
        if ($this->templateString !== null && $this->templateFile !== null) {
            throw new \Exception("You must provide a template string or a template file, not both");
        }
        if( $this->templateFile !== null && !file_exists($this->templateFile) ){
            throw new \Exception("Template file not found: " . $this->templateFile);
        }
        if ($this->templateString === null) {
            $this->templateString = file_get_contents($this->templateFile);
        }

        $this->documents = $this->_parseSingleTemplate($this->templateString);
    }

    private $documents = [];

    /**
     * @param $input
     * @param int $lineStart
     * @param int $docNo
     * @return Document[]
     */
    private function _parseSingleTemplate($input, int $lineStart=0, int $docNo=1) : array
    {
        $retDocs = [];
        // Search for liquid template yaml section until next ---
        if (preg_match('/^---\n(.*?)\n---\n(.*?)(?=\n---|\Z)/sm', $input, $matches)) {
            $yaml = $matches[1];
            $content = $matches[2];
            // Detect yaml error message
            try {
                $yaml = phore_yaml_decode($yaml);
            } catch (\Exception $e) {
                throw new \InvalidArgumentException("Invalid yaml header in document #$docNo starting at line " . $lineStart . ": " . $e->getMessage());
            }


            $retDocs[] = new Document($yaml, $content);
            $restText = substr($input, strlen($matches[0]));

            if (trim ($restText) !== "")
                $retDocs = array_merge($retDocs, $this->_parseSingleTemplate(ltrim($restText), $lineStart + substr_count($matches[0], "\n"), $docNo+1));
            return $retDocs;
        } else {
            throw new \InvalidArgumentException("Invalid template format in document #$docNo starting at line " . $lineStart . ": Garbage in input: '". $input . "'");
        }

    }


    public function getDocuments()
    {
        return $this->documents;
    }


    /**
     * @param array $data
     * @return Document[]
     */
    public function render(array $data): array
    {
        $ret = [];
        foreach ($this->documents as $doc) {
            $ret[] = new Document($doc->metaData, $this->renderer->render($doc->content, $data));
        }
        return $ret;
    }

    public function __toString(): string
    {
        // TODO: Implement __toString() method.
    }


}
