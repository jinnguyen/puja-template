<?php
namespace Puja\Template\Lexer\Tag;

/**
 * Compile for Customer Tag
 * Class CCompile
 * @package Puja\Template\Lexer\Tag
 */

class CCompile extends TagAbstract
{
    protected function init()
    {
        $content = $this->parser->getTplContent();
        $customTags = $this->parser->getCustomTag();
        if (empty($customTags)) {
            return true;
        }
        foreach (array_flip($customTags) as $key => $v) {
            $this->parser->addAstHeader('$GLOBALS[\'' . $key . 'Tag\'] = $this->tags[\'' . $key . '\'];');
        }
        preg_match_all('/\{\%\s?(' . implode('|', array_keys($customTags)) . ')\s+(.*?)\s?\%\}/i', $content, $this->matches);
    }

    public function compile()
    {
        $content = $this->parser->getTplContent();
        if (empty($this->matches[1])) {
            return $content;
        }
        $parserTags = $this->parser->getCustomTag();

        foreach ($this->matches[1] as $key => $tag) {
            $this->matches[2][$key] = '\';' . $this->parser->getAstVar(false) . '.=$GLOBALS[\'' . $parserTags[$tag] . 'Tag\']->' . $tag . 'Tag(\'' . $this->matches[2][$key] . '\');' . $this->parser->getAstVar();
        }

        return str_replace($this->matches[0], $this->matches[2], $content);
    }
}