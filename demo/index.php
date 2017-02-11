<?php

include __DIR__ . '/../vendor/autoload.php';

class CustomFilter extends \Puja\Template\Lexer\Filter\FilterAbstract
{
    public function dateFilter($var, $args) {
        return abs($var);
    }
}

class CustomTag extends \Puja\Template\Lexer\Tag\TagAbstract
{
    public function cssTag($arg)
    {
        return '<style src="' . $arg . '" />';
    }

    public function javascriptTag($arg)
    {
        return '<style src="' . $arg . '" />';
    }
}

$tpl = new \Puja\Template\Template([
    'templateDirs' => [__DIR__ . '/templates/Default', __DIR__ . '/templates/2017'],
    'cacheDir' => __DIR__ . '/cache/',
    'cacheLevel' => 0,
    'customTag' => 'CustomTag',
    'customFilter' => 'CustomFilter',
    'debug' => true,
]);

$a = new stdClass();
$a->b = ['test1' => 8, 'test2' => 10];
$a->c = 6;
//$tpl->setCustomTag('Puja\\Template\\Lexer\\Tag\\Custom');
$tpl->parse('test.tpl', array('skin' => 'default', 'a' => ['b' => ['test1' => 9, 'test2' => ['e','d','k']], 'c' => 8]));