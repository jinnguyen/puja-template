<?php
namespace Puja\Template\Lexer\Variable;

class Variable extends \Puja\Template\Lexer\LexerAbstract
{
    protected $errors = array(
        'missing_tag' => 'Missing {{ or }}',
    );

    protected $operatorFn = array(' in ' => 'in_array');
    protected $operators = array(' and ', ' or ', ' not ', ' is ', ',', ' in ', '%', '!==', '!=', '>=', '<=', '===', '==', '<>', '>', '<', '&&', '||', '!', '+', '-', '*', '/', '=', ';');
    protected $replacedOperators = array();
    public function compile()
    {
        $variableNames = $this->buildVariableName($this->matches[1], true, true);
        foreach ($this->matches[1] as $key => $var) {
            $this->matches[1][$key] = '\';' . $this->parser->getAstVar(false) . '.=' . $variableNames[$var] . ';' . $this->parser->getAstVar();
        }
        return str_replace($this->matches[0], $this->matches[1], $this->parser->getTplContent());
    }

    protected function init()
    {
        foreach ($this->operators as $key => $v) {
            if (array_key_exists($v, $this->operatorFn)) {
                $this->replacedOperators[$key] = $this->parser->getHashName($this->operatorFn[$v]);
            } else {
                $this->replacedOperators[$key] = $this->parser->getHashName('operator_index_' . $key);
            }
        }

        $this->parser->addAstHeader('use Puja\\Template\\Lexer\\Variable\\Variable;');
        preg_match_all('/\{\{\s*([^\{\}]*?)\s*\}\}/', $this->parser->getTplContent(), $this->matches);
    }

    public function buildVariableName($variables = array(), $checkIsset = true, $checkArrayObj = false)
    {
        $return = array();
        foreach ($variables as $variable) {
            if ($variable === null) {
                continue;
            }
            $return[$variable] = $this->buildComplexVar($variable, $checkIsset, $checkArrayObj);
        }
        return $return;
    }

    protected function buildComplexVar($var, $checkIsset = true, $checkArrayObj = false)
    {
        $filters = explode('|', $var);
        $var = $filters[0];
        unset($filters[0]);

        $var = str_replace($this->operators, $this->replacedOperators, trim($var));
        $vars = explode($this->parser->getHashSeparator(), $var);
        
        foreach ($vars as $key => $var) {
            if ($this->parser->isHashName($var)) {
                $vars[$key] = $this->getOperator($this->parser->convertHashName($var));
            } else {
                $vars[$key] = $this->buildSimpleVar($var, $checkIsset, $checkArrayObj);
            }
        }

        // modify for in_array
        foreach ($vars as $key => $var) {
            if (in_array($var, $this->operatorFn)) {
                $varInArray = '$inarray' . $this->parser->getHashSeparator() . $key;
                $vars[$key - 1] = $varInArray . '=' . $vars[$key + 1] . '){} if(' . $varInArray .  '&& ' . $var . '(' . $vars[$key - 1];
                $vars[$key] = ',';
                $vars[$key + 1] = $varInArray . ')';
            }
        }
        
        $var = implode(' ' , $vars);
        $parserFilters = $this->parser->getCustomFilter();
        if (!empty($filters)) {
            foreach ($filters as $filter) {
                list ($fn, $param) = explode(':', $filter . ':');
                if (empty($parserFilters[$fn])) {
                    continue;
                }

                $var = '$GLOBALS[\'' . $parserFilters[$fn] . 'Filter\']->' . $fn . 'Filter(' . $var . ', \'' . trim($param, '"\\\'') . '\')';
            }
        }
        return $var;
    }

    protected function getOperator($operationStringIndex)
    {
        if (in_array($operationStringIndex, $this->operatorFn)) {
            return $operationStringIndex;
        }

        $operators = $this->operators;
        $operators[0] = '&&';
        $operators[1] = '||';
        $operators[2] = '!';
        $operators[3] = '==';
        $operators[4] = '=>';
        $index = str_replace('operator_index_', '', $operationStringIndex);

        return $operators[$index];
    }

    protected function buildSimpleVar($var, $checkIsset = true, $checkArrayObj = false)
    {
        $var = stripcslashes(trim($var));
        $firstChar = substr($var, 0, 1);
        if ($firstChar == '"' || $firstChar == '\'' || is_numeric($firstChar)) {
            return $var;
        }
        $vars = explode('.', trim($var));
        $var = '$' . $vars[0];
        unset($vars[0]);

        if ($checkArrayObj && count($vars)) {
            return 'Variable::getVar(isset(' . $var . ')?' . $var . ':null, \'' . implode(',', $vars) . '\', true)';
        }

        if (count($vars)) {
            $var .= '[\'' . implode('\'][\'', $vars) . '\']';
        }

        if ($checkIsset) {
            return '(isset(' . $var . ')?' . $var . ':null)';
        }

        return $var;
    }

    public static function getVar($data, $keys, $toString = false)
    {
        if (empty($data) || empty($keys) || (!is_object($data) &&  !is_array($data))) {
            return $data;
        }

        $keys = explode(',', $keys);
        foreach ($keys as $key) {
            $dataObj = new \ArrayObject($data);

            if (!$dataObj->offsetExists($key)) {
                return null;
            }
            $data = $dataObj->offsetGet($key);
            if (!is_object($data) && !is_array($data)) {
                return $data;
            }
        }

        if ($toString && (is_array($data) || is_object($data))) {
            //return print_r($data, true);
        }

        return $data;
    }

    public function debug($fileName, $fileContent)
    {
        preg_match_all('/\{\{\s*([^\{\{]*?)\s*\}\}/i', $fileContent, $matches);

        if ($matches[0]) {
            foreach ($matches[0] as $key => $val) {
                $matches[1][$key] = $this->parser->getHashName('variablesyntax' . $key);
            }
            $fileContent = str_replace($matches[0], $matches[1], $fileContent);
        }

        if (strpos('_' . $fileContent, '{{') || strpos('_' . $fileContent, '}}')) {
            $fileContent = str_replace(
                array('{{', '}}'),
                array($this->getDebugElm('{{'), $this->getDebugElm('}}')),
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