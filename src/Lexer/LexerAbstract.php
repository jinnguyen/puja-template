<?php
namespace Puja\Template\Lexer;
use Puja\Template\Exception;
abstract class LexerAbstract
{
    protected $matches;
    protected $tplObj;
    protected $parser;
    protected $errors = array(
        'file_not_found' => 'File %s is not found',
        'invalid_syntax' => 'Invalid syntax (%s)'
    );
    public function __construct(\Puja\Template\Template $tplObj, \Puja\Template\Parser $parser, $init = true)
    {
        $this->matches = array();
        $this->tplObj = $tplObj;
        $this->parser = $parser;
        if ($init) {
            $this->init();
        }
    }

    public function getClassName()
    {
        return get_class($this);
    }
    
    protected function init() {}
    public function compile() {}
    public function afterCompile() {}
    public function debug($fileName, $fileContent) {}
    protected function getDebugElm($el)
    {
        return '<span class="current_bug">' . $el . '</span>';
    }

    protected function getDebugData($error, $trace)
    {
        return array(
            'error' => $error,
            'trace' => $trace
        );
    }

    protected function debugFileExists($matches, $fileContent, $multiFile = false)
    {
        $matches['replace'] = $matches[0];
        // check file exists
        foreach ($matches[1] as $key => $includeFile) {
            $errorFile = $includeFile;
            try {
                if ($multiFile) {
                    $files = explode(' ', $includeFile);
                    foreach ($files as $file) {
                        $errorFile = $file;
                        $this->parser->getFileContent($file, false);
                    }
                } else {
                    $this->parser->getFileContent($includeFile, false);
                }
            } catch (Exception $e) {
                $matches['replace'][$key] = $this->getDebugElm($matches['replace'][$key]);
                return $this->getDebugData(
                    sprintf($this->errors['file_not_found'], $errorFile),
                    str_replace($matches[0], $matches['replace'], $fileContent)
                );
            }
        }
    }

    protected function debugAssignVars($matches, $fileContent)
    {
        if (empty($matches['variables'])) {
            throw new Exception('Missing $matches[variables]');
            
        }
        $matches['replace'] = $matches[0];
        foreach ($matches['variables'] as $key => $var) {
            if (empty($var)) {
                continue;
            }
            $var = preg_replace('/\s+=\s+/is', '=', trim($var));
            $var = str_replace('\\\'', '\'', $var);
            $var = str_replace('\'\'', $this->parser->getHashName('EmptyVarName'), $var);
            $var = str_replace('""', $this->parser->getHashName('EmptyVarName'), $var);
            $var = preg_replace('/\'(.*?)\'/is', $this->parser->getHashName('debug_var'), $var);
            $var = preg_replace('/\"(.*?)\"/is', $this->parser->getHashName('debug_var'), $var);
            $var = preg_replace('/\s+/is', ' ', trim($var));

            $vars = explode(' ', $var);
            foreach ($vars as $v) {
                list($varName, $varValue) = explode('=', $v . '=');
                if ($varName == '' || $varName == $this->parser->getHashName('EmptyVarName') || $varName == $this->parser->getHashName('debug_var') || $varValue == '' || is_numeric($varName) || strpos('_' . $varValue, '\'') || strpos('_' . $varValue, '"') ||strpos('_' . $varName, '\'') || strpos('_' . $varName, '"')) {
                    $matches['replace'][$key] = $this->getDebugElm($matches['replace'][$key]);
                    return $this->getDebugData(
                        sprintf($this->errors['invalid_syntax'], $v),
                        str_replace($matches[0], $matches['replace'], $fileContent)
                    );
                }
            }
        }
    }

    public function debugIfAndFor($matches, $fileContent)
    {
        if (empty($matches[1])) {
            return;
        }
        foreach ($matches[1] as $key => $tag) {
            $matches['replace'][$key] = $this->getDebugElm($matches[0][$key]);
            switch ($tag) {
                case 'if':
                    $matches[1][$key] = '\';if(true){' . $this->parser->getAstVar();
                    break;
                case 'elseif':
                    $matches[1][$key] = '\';}elseif(true){' . $this->parser->getAstVar();
                    break;
                case 'else':
                    $matches[1][$key] = '\';}else{' . $this->parser->getAstVar();
                    break;
                case 'endif':
                    $matches[1][$key] = '\';}' . $this->parser->getAstVar();
                    break;
                case 'for':
                    $matches[1][$key] = '\'; if ($forArr = array(1,2,3)) {foreach($forArr as $key => $val) {' . $this->parser->getAstVar();
                    break;
                case 'empty':
                    $matches[1][$key] = '\';}}else{if(true){' . $this->parser->getAstVar();
                    break;
                case 'endfor':
                    $matches[1][$key] = '\';}}' . $this->parser->getAstVar();
                    break;
            }
        }

        $evalText = str_replace($matches[0], $matches[1], $fileContent);
        $evalText = $this->parser->getAstVar(false) . '=\'' . $evalText . '\';';
        
        $fileContent = str_replace($matches[0], $matches['replace'], $fileContent);
        try {
            $check = false;
            @eval($evalText);
        } catch (Exception $e) {
            
        }
        $lastError = error_get_last();
        if ($lastError) {
            return $this->getDebugData(
                $lastError['message'],
                $fileContent
            );
        }
    }
}