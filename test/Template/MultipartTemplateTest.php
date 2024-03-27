<?php

namespace Lack\Test\Kiki\Template;

use Lack\KiKi\Helper\Template\MultipartTemplate;
use PHPUnit\Framework\TestCase;

class MultipartTemplateTest extends TestCase
{

    public function testMultipartTemplateSingleTemplate()
    {

        $template = new MultipartTemplate(templateFile: __DIR__ . "/single-tpl.txt");


        $docs = $template->render([
            "name" => "John Doe",
            "age" => 30
        ]);

        print_r ($template->getDocuments());
    }


        public function testMultipartTemplateMultiTemplate()
    {

        $template = new MultipartTemplate(templateFile: __DIR__ . "/multi-tpl.txt");


        $docs = $template->render([
            "name" => "John Doe",
            "age" => 30
        ]);

        print_r ($template->getDocuments());
    }
}
