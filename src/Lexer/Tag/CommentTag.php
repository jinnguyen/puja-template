<?php
namespace Puja\Template\Lexer\Tag;

class CommentTag extends TagAbstract
{

    protected $errors = array(
        'missing_tag' => 'Missing {# or #}',
    );
    
    public function compile()
    {
        $content = func_get_arg(0);
        //$content = addslashes($content);
        $content = preg_replace('/\{\#\s?(.*?)\s?\#\}/i', '', $content);
        return $content;
    }

    public function debug($fileName, $fileContent)
    {
        preg_match_all('/\{\#\s*([^\{\#]*?)\s*\#\}/i', $fileContent, $matches);

        if ($matches[0]) {
            foreach ($matches[0] as $key => $val) {
                $matches[1][$key] = $this->parser->getHashName('commentblock_' . $key);
            }
            $fileContent = str_replace($matches[0], $matches[1], $fileContent);
        }

        if (strpos('_' . $fileContent, '{#') || strpos('_' . $fileContent, '#}')) {
            $fileContent = str_replace(
                array('{#', '#}'),
                array($this->getDebugElm('{#'), $this->getDebugElm('#}')),
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