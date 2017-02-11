<?php
namespace Puja\Template\Lexer\Tag;

class ForTag extends TagAbstract
{
    protected $tagNames = array('for', 'empty', 'endfor');

    protected function init()
    {
        $content = $this->parser->getTplContent();
        preg_match_all('/\{\%\s*for\s*([a-z0-9\_\,\s]*?)\s+in\s+([a-z0-9\.\_]*?)\s*\%\}/is', $content, $this->matches['FOR']);
        //$this->matches['FOR'] = $matches;

        preg_match_all('/\{\%\s*(empty|endfor)\s+(.*?)\s*\%\}/', $content, $this->matches['OTHERS']);
        //$this->matches['OTHERS'] = $matches;
    }

    public function compile()
    {
        $content = $this->parser->getTplContent();

        if (empty($this->matches['FOR'][2])) {
            return null;
        }

        $variableNames = $this->parser->buildVariableName(array_merge($this->matches['FOR'][2]), true, true);
        $variableAssignName = $this->parser->buildVariableName(array_merge($this->matches['FOR'][1]), false, false);;

        foreach ($this->matches['FOR'][2] as $key => $varName) {
            $this->matches['FOR'][2][$key] = '\'; if ($foreach' . $this->parser->getHashSeparator() . $key . '=' . $variableNames[$varName] . ') {foreach($foreach' . $this->parser->getHashSeparator() . $key . ' as ' . $variableAssignName[$this->matches['FOR'][1][$key]] . '){' . $this->parser->getAstVar();
        }

        $content = str_replace($this->matches['FOR'][0], $this->matches['FOR'][2], $content);

        if (empty($this->matches['OTHERS'][1])) {
            return null;
        }
        
        foreach ($this->matches['OTHERS'][1] as $key => $tagName) {
            switch (trim($tagName)) {
                case 'empty':
                    $this->matches['OTHERS'][1][$key] = '\';}}else{if(true){' . $this->parser->getAstVar();
                    break;
                case 'endfor':
                    $this->matches['OTHERS'][1][$key] = '\';}}' . $this->parser->getAstVar();
                    break;
            }
        }

        return str_replace($this->matches['OTHERS'][0], $this->matches['OTHERS'][1], $content);
    }

    public function debug($fileName, $fileContent)
    {
        
        preg_match_all('/\{\%\s*(' . implode('|', $this->tagNames) . ')\s+(.*?)\s*\%\}/', $fileContent, $matches);

        $debugIf = $this->debugIfAndFor($matches, $fileContent);
        if ($debugIf) {
            return $debugIf;
        }
    }
}