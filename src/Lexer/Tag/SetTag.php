<?php
namespace Puja\Template\Lexer\Tag;

class SetTag extends TagAbstract
{
    protected $tagNames = array('set');
    protected $includeContents = array();

    protected function init()
    {
        $content = $this->parser->getTplContent();
        preg_match_all('/\{\%\s?set\s+(.*?)\s?\%\}/i', $content, $this->matches);
    }

    public function compile()
    {
        if (empty($this->matches[1])) {
            return;
        }
        $content = $this->parser->getTplContent();
        foreach ($this->matches[1] as $key => $var) {
            $params = $this->getAssignVars($var);
            $this->matches[1][$key] = '\';' . implode(';', $params) . ';' . $this->parser->getAstVar();
        }
        
        return str_replace($this->matches[0], $this->matches[1], $content);
    }

    public function debug($fileName, $fileContent)
    {
        preg_match_all('/\{\%\s?set\s+(.*?)\s?\%\}/i', $fileContent, $matches);
        if (empty($matches[0])) {
            return;
        }
        
        $matches['variables'] = $matches[1];
        $debugAssignVars = $this->debugAssignVars($matches, $fileContent);
        if ($debugAssignVars) {
            return $debugAssignVars;
        }
    }


}