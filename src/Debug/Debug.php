<?php
namespace Puja\Template\Debug;
use Puja\Stdlib\File;

class Debug
{
    protected $tplObj;
    protected $parser;
    protected $isDebug;
    public function __construct(\Puja\Template\Template $tplObj, \Puja\Template\Parser $parser, $isDebug = true)
    {
        $this->tplObj = $tplObj;
        $this->parser = $parser;
        $this->isDebug = $isDebug;
    }

    public function beforeDebug($fileName, $fileContent)
    {
        if (empty($this->isDebug)) {
            return true;
        }
        
        $lexers = $this->parser->getLexers();
        foreach ($lexers['static'] as $lexer) {
            $errors = $lexer->debug($fileName, $fileContent);
            if ($errors) {
                $this->showDebug($fileName, $errors['error'], $errors['trace']);
            }
        }
    }

    public function debug($fileName, $fileContent)
    {
        if (empty($this->isDebug)) {
            return true;
        }

        $lexers = $this->parser->getLexers();
        foreach ($lexers['init'] as $lexerName) {
            $lexer = new $lexerName($this->tplObj, $this->parser, false);
            $errors = $lexer->debug($fileName, $fileContent);
            if ($errors) {
                $this->showDebug($fileName, $errors['error'], $errors['trace']);
            }
        }

        // debug combine if and for
        $forLexer = new \Puja\Template\Lexer\Tag\ForTag($this->tplObj, $this->parser, false);
        $ifLexer = new \Puja\Template\Lexer\Tag\IfTag($this->tplObj, $this->parser, false);
        $tagNames = $forLexer->getTagNames() + $ifLexer->getTagNames();
        preg_match_all('/\{\%\s*(' . implode('|', array_keys($tagNames)) . ')\s+(.*?)\s*\%\}/', $fileContent, $matches);

        $errors = $forLexer->debugIfAndFor($matches, $fileContent);
        if ($errors) {
            $this->showDebug($fileName, $errors['error'], $errors['trace']);
        }
    }

    protected function showDebug($fileName, $errorMessage, $errorTrace)
    {
        $debugHtml = '';
        $fp = new File\Info(dirname(__FILE__) . '/debug.html');
        if ($fp->isFile()) {
            $debugHtml = $fp->getContent();
        } else {
            throw new Exception('File debug.html couldnt find');
        }

        $fileLines = array();
        $errorTrace = stripcslashes($errorTrace);
        $errorTrace = explode(PHP_EOL, $errorTrace);
        foreach ($errorTrace as $line => $row) {
            if (strpos($row, 'class="current_bug"')) {
                $fileLines[] = $line + 1;
            }

            $errorTrace[$line] = '<li>' . $row . '</li>';
        }

        echo str_replace(
            array('{{error_message}}', '{{tpl_file}}', '{{line}}', '{{error_body}}'),
            array($errorMessage, $fileName, implode(', ', $fileLines), implode(PHP_EOL, $errorTrace)),
            $debugHtml
        );
        exit;
    }
}