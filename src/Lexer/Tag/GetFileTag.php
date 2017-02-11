<?php
namespace Puja\Template\Lexer\Tag;

class GetFileTag extends TagAbstract
{
    protected $tagNames = array('get_file');
    protected $includeContents = array();

    protected function init()
    {
        $content = $this->parser->getTplContent();

        preg_match_all('/\{\%\s?get_file\s+(.*?)\s?\%\}/i', $content, $this->matches);
    }

    public function afterCompile()
    {
        $content = $this->parser->getTplContent();
        if (empty($this->matches[1])) {
            return null;
        }

        foreach ($this->matches[1] as $key => $includeFile) {
            //preg_replace
            if (!array_key_exists($includeFile, $this->includeContents)) {
                $this->includeContents[$includeFile] = $this->getMultiFileContent($includeFile);
            }
            $this->matches[1][$key] = $this->includeContents[$includeFile];
        }
        $content = str_replace($this->matches[0], $this->matches[1], $content);

        return $content;
    }

    protected function getMultiFileContent($file)
    {
        $files = explode(' ', trim($file));
        $content = '';
        foreach ($files as $file) {
            if (empty($file)) {
                continue;
            }
            $content .= $this->parser->getFileContent($file);
        }

        return $content;
    }

    public function debug($fileName, $fileContent)
    {

        preg_match_all('/\{\%\s?get_file\s+(.*?)\s?\%\}/i', $fileContent, $matches);

        if (empty($matches[0])) {
            return;
        }

        $debugFileExists = $this->debugFileExists($matches, $fileContent, true);
        if ($debugFileExists) {
            return $debugFileExists;
        }
    }
}