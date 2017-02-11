<?php
namespace Puja\Template\Lexer\Filter;

class FilterAbstract extends \Puja\Template\Lexer\LexerAbstract
{
    protected $filterNames = array();

    public function getFilterNames()
    {
        $clsName = get_class($this);
        if (!empty($this->filterNames)) {
            return array_fill_keys($this->filterNames, $clsName);
        }

        $methods = get_class_methods($this);
        foreach ($methods as $method) {
            if (substr($method, -6) === 'Filter') {
                $filter = substr($method, 0, -6);
                $this->filterNames[$filter] = $clsName;
            }
        }

        return $this->filterNames;
    }
}