<?php

namespace Lack\KiKi\Helper\Template;

class TemplateRenderer
{

    public function __construct(
        private array $functions = []
    )
    {
    }

    public function addFunction($name, callable $function)
    {
        $this->functions[$name] = $function;
    }


    public function _renderIf(string $template, array $data) : string {
        $render = function ($template, $data) use (&$render) {
            $result = preg_replace_callback('/{{\s*if\s+([a-zA-Z0-9_]+)\s*}}(.*?){{\s*endif\s*}}/sm', function ($matches) use ($data, $render) {
                $key = $matches[1];
                if (isset($data[$key]) && $data[$key]) {
                    return $render($matches[2], $data);
                }
                return '';
            }, $template);
            return $result;
        };
        return $render($template, $data);
    }

    /**
     * Interpret "filterName: "param1", "param2"" in template
     *
     * @param string $template
     * @param array $data
     * @return string
     */
    public function __invokeSingleFilter(string $filterString, mixed $value, array $data) : string {
        [$filterName, $filterParams] = explode(":", $filterString, 2);
        $filterName = trim($filterName);

        // Params can be named params like key="value", key2="value2" or just a list of values like "value1", "value2" or mixed
        // return a array with named params and indexed params. Use correct escaping for values

        $params = [];
        $filterParams = explode(",", $filterParams);
        foreach ($filterParams as $param) {
            $param = trim($param);
            // Allow Backslash escaped ", " and "\:"

            if (preg_match('/^([a-zA-Z0-9_]+)\s*=\s*"([^"]+)"$/', $param, $matches)) {
                $params[$matches[1]] = $matches[2];
            } else {
                $params[] = $param;
            }
        }

        if ( ! isset($this->functions[$filterName])) {
            throw new \InvalidArgumentException("Filter '$filterName' not defined");
        }

    }

    /**
     * Interpret {{ key }} and {{ key | filter }} in template
     *
     * also allows {{ key | filter1 | filter2 }} and {{ key | filter1 | filter2 | filter3 }}
     *
     * And Filter with parameters like {{ key | filter: key="param1", key2="param2" }}
     *
     * @param string $template
     * @param array $data
     * @return string
     */
    public function _fillValue(string $template, array $data) : string
    {
        $render = function ($template, $data) use (&$render) {
            $result = preg_replace_callback('/{{\s*([a-zA-Z0-9_]+)(?:\s*\|\s*([a-zA-Z0-9_]+(?:\s*:\s*[^}]+)?))+\s*}}/m', function ($matches) use ($data, $render) {
                $key = $matches[1];
                $value = $data[$key] ?? "";
                foreach (array_slice($matches, 2) as $filter) {
                    $value = $this->__invokeSingleFilter($filter, $value, $data);
                }
                return $value;
            }, $template);
            return $result;
        };
        return $render($template, $data);

    }



    public function render(string $template, array $data = []): string
    {
        $template = $this->_renderIf($template, $data);
        $template = $this->_fillValue($template, $data);
        return $template;
    }

}
