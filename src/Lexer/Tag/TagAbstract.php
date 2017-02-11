<?php
namespace Puja\Template\Lexer\Tag;

class TagAbstract extends \Puja\Template\Lexer\LexerAbstract
{
    protected $tagNames;

    public function getTagNames()
    {
        $clsName = $this->getClassName();
        if (!empty($this->tagNames)) {
            return array_fill_keys($this->tagNames, $clsName);
        }

        $this->tagNames = array();
        $methods = get_class_methods($this);
        foreach ($methods as $method) {
            if (substr($method, -3) === 'Tag') {
                $tag = substr($method, 0, -3);
                $this->tagNames[$tag] = $clsName;
            }
        }

        return $this->tagNames;
    }

    protected function getAssignVars($var)
    {
        $var = preg_replace('/\s+=\s+/is', '=', trim($var));
        $var = str_replace('\\\'', '\'', $var);
        preg_match_all('/\'(.*?)\'/is', $var, $squoteMatches);
        preg_match_all('/\"(.*?)\"/is', $var, $dquoteMatches);

        $quoteMatches[0] = array_merge($squoteMatches[0], $dquoteMatches[0]);
        $quoteMatches[1] = array_merge($squoteMatches[1], $dquoteMatches[1]);

        if (!empty($quoteMatches[0])) {
            foreach ($quoteMatches[1] as $key => $v) {
                $quoteMatches[2][$key] = '\'puja_assign_var_quoteindex_' . $key . '\'';
            }
            $var = str_replace($quoteMatches[0], $quoteMatches[2], $var);
        }

        $var = str_replace(array(' ', '[', ']'), array('&', $this->parser->getHashSeparator() . 'lsquaredbracket', $this->parser->getHashSeparator() . 'rsquaredbracket'), $var);
        parse_str($var, $params);
        $variableName = $this->parser->buildVariableName($params, true, true);

        $return = array();
        foreach ($params as $key => $val) {
            $key = str_replace(
                array($this->parser->getHashSeparator() . 'lsquaredbracket',
                    $this->parser->getHashSeparator() . 'rsquaredbracket'),
                array('[', ']'),
                $key
            );
            $return['$' . $key] = '$' . $key . '=' . (empty($quoteMatches[2]) ? $variableName[$val] : str_replace($quoteMatches[2], $quoteMatches[0], $variableName[$val]));
        }

        return $return;
    }

    
}