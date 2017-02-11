<?php
namespace Puja\Template\Lexer\Tag;

class IfTag extends TagAbstract
{
    protected $tagNames = array('if', 'elseif', 'else', 'endif');

    protected function init()
    {
        $content = $this->parser->getTplContent();
        preg_match_all('/\{\%\s*(' . implode('|', $this->tagNames) . ')\s+(.*?)\s*\%\}/', $content, $this->matches);
    }

    public function compile()
    {
        $content = $this->parser->getTplContent();

        if (0 === count($this->matches[1])) {
            return $content;
        }

        $conditions = $this->parser->buildVariableName($this->matches[2], true, true);

        foreach ($this->matches[1] as $key => $tagName) {
            switch (trim($tagName)) {
                case 'if':
                    $this->matches[1][$key] = '\';' . $tagName . '(' . $conditions[$this->matches[2][$key]] . '){' . $this->parser->getAstVar();
                    break;
                case 'elseif':
                    $this->matches[1][$key] = '\';}' . $tagName . '(' . $conditions[$this->matches[2][$key]] . '){' . $this->parser->getAstVar();
                    break;
                case 'else':
                    $this->matches[1][$key] = '\';}else{' . $this->parser->getAstVar();
                    break;
                case 'endif':
                    $this->matches[1][$key] = '\';} ' . $this->parser->getAstVar();
                    break;
            }
        }
        
        return str_replace($this->matches[0], $this->matches[1], $content);
    }

    public function debug($fileName, $fileContent)
    {
        return;
        
        preg_match_all('/\{\%\s*(' . implode('|', $this->tagNames) . ')\s+(.*?)\s*\%\}/', $fileContent, $matches);

        $debugIf = $this->debugIfAndFor($matches, $fileContent);
        if ($debugIf) {
            return $debugIf;
        }
    }
}