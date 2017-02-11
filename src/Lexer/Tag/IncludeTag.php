<?php
namespace Puja\Template\Lexer\Tag;
class IncludeTag extends TagAbstract
{
    protected $tagNames = array('include');
    protected $includeContents = array();

    protected function init()
    {
        $content = $this->parser->getTplContent();

        preg_match_all('/\{\%\s?include\s+(.*?)\s+(.*?)\s?\%\}/i', $content, $matches);
        if (0 === count($matches[1])) {
            return;
        }

        $replaces = array();

        foreach ($matches[1] as $key => $includeFile) {
            //preg_replace
            if (!array_key_exists($includeFile, $this->includeContents)) {
                $this->includeContents[$includeFile] = $this->parser->getFileContent($includeFile);
            }
            $replaces[$key] = $this->includeContents[$includeFile];
            $setVariable = trim($matches[2][$key]);
            if ($setVariable) {
                $replaces[$key] = '{% ' . $this->parser->getHashSeparator() . 'beforeinclude ' . $setVariable . ' %}' .
                    $this->includeContents[$includeFile] . '{% ' . $this->parser->getHashSeparator() . 'afterinclude %}';

            }
        }
        $content = str_replace($matches[0], $replaces, $content);

        $this->parser->setTplContent($content);
        return;
    }

    public function afterCompile()
    {
        $content = $this->parser->getTplContent();
        preg_match_all('/\{\%\s?' . $this->parser->getHashSeparator() . 'beforeinclude\s+(.*?)\s?\%\}/i', $content, $beforeIncludes);

        if (empty($beforeIncludes[1])) {
            return;
        }


        preg_match_all('/\{\%\s?' . $this->parser->getHashSeparator() . 'afterinclude\s?\%\}/i', $content, $afterIncludes);
        if (count($beforeIncludes[0]) !== count($afterIncludes[0])) {
            return;
        }

        $afterIncludes[1] = array();
        foreach ($beforeIncludes[1] as $key => $var) {
            $params = $this->getAssignVars($var);
            $beforeIncludes[1][$key] = '\';function fn' . $key . $this->parser->getHashSeparator() . 'includeTpl(' . implode(',', array_keys($params)) . '){ ' . $this->parser->getAstVar(false) . ' =\'';

            $afterIncludes[1][$key] = '\';return ' . $this->parser->getAstVar(false) . ';} ' . $this->parser->getAstVar(false) . ' .= fn' . $key . $this->parser->getHashSeparator() . 'includeTpl(' . implode(',', $params) . ');' . $this->parser->getAstVar();
            //
        }

        $content = str_replace($beforeIncludes[0], $beforeIncludes[1], $content);
        $content = str_replace($afterIncludes[0], $afterIncludes[1], $content);
        return $content;
    }

    public function debug($fileName, $fileContent)
    {
        preg_match_all('/\{\%\s?include\s+(.*?)\s+(.*?)\s?\%\}/i', $fileContent, $matches);
        if (empty($matches[0])) {
            return;
        }

        $matches['variables'] = $matches[2];
        $debugAssignVars = $this->debugAssignVars($matches, $fileContent);
        if ($debugAssignVars) {
            return $debugAssignVars;
        }

        $debugFileExists = $this->debugFileExists($matches, $fileContent);
        if ($debugFileExists) {
            return $debugFileExists;
        }
    }

}