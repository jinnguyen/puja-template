<?php
namespace Puja\Template\Lexer\Filter;

class CCompile extends FilterAbstract
{
    protected function init()
    {
        $filters =  array_flip($this->parser->getCustomFilter());
        foreach ($filters as $key => $v) {
            $this->parser->addAstHeader('$GLOBALS[\'' . $key . 'Filter\'] = $this->filters[\'' . $key . '\'];');
        }
    }
}