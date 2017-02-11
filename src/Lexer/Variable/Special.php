<?php
namespace Puja\Template\Lexer\Variable;

class Special extends \Puja\Template\Lexer\LexerAbstract
{
    protected $errors = array(
        'missing_tag' => 'Missing {$ or $}',
    );

    protected function setUp()
    {
        die('kkkk');
    }
    
    public function compile()
    {
        $content = func_get_arg(0);
        return preg_replace_callback(
            '/\{\$\s*([a-z0-9\_]*?)\s*\$\}/i',
            array($this, 'callbackFn'),
            $content
        );
    }

    protected function callbackFn($matches)
    {
        $tplData = $this->parser->getData();
        return empty($tplData[$matches[1]]) ? null : $tplData[$matches[1]];
    }

    public function debug($fileName, $fileContent)
    {
        preg_match_all('/\{\$\s*([^\{\$]*?)\s*\$\}/i', $fileContent, $matches);

        if ($matches[0]) {
            foreach ($matches[0] as $key => $val) {
                $matches[1][$key] = $this->parser->getHashName('specialvariable_' . $key);
            }
            $fileContent = str_replace($matches[0], $matches[1], $fileContent);
        }

        if (strpos('_' . $fileContent, '{$') || strpos('_' . $fileContent, '$}')) {
            $fileContent = str_replace(
                array('{$', '$}'),
                array($this->getDebugElm('{$'), $this->getDebugElm('$}')),
                $fileContent
            );
            return $this->getDebugData(
                $this->errors['missing_tag'],
                str_replace($matches[1], $matches[0], $fileContent)
            );
        }
        
        return null;
    }
}