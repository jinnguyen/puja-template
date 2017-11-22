<?php
namespace Puja\Template\Lexer\Filter;

class Builtin extends FilterAbstract
{
    public function absFilter($var, $args) {
        return abs($var);
    }

    public function capfirstFilter($var, $arg = null)
    {
        if (empty($var)) {
        	return $var;
        }

        return ucfirst($var);
    }

    public function dateFilter($var, $arg = 'Y-m-d h:i:s')
    {
        if (is_numeric($var)) {
        	return date($var, $arg);
        }

        return date($arg, strtotime($var));
    }

    public function defaultFilter($var, $arg = null)
    {

        if ($var === null) {
        	return $arg;
        }

        return $var;
    }

    public function escape($var)
    {
        $replace_table = array(
            '&' => '&amp;',
            '"' => '&quot;',
            '\'' => '&#39;',
            '>' => '&gt;',
            '<' => '&lt;'
        );
        return str_replace(array_keys($replace_table), $replace_table, $var);
    }

    public function escapejsFilter($var, $arg = null)
    {
        return str_replace("\n", "\\\n", $var);
    }

    public function joinFilter($var, $arg = '')
    {
        if (is_array($var)) {
        	return implode($arg, $var);
        }

        return $var;
    }

    public function keysFilter($var, $arg = null)
    {
        if (is_array($var)) {
        	return array_keys($var);
        }

        return array();
    }

    public function length($val)
    {
        if (is_array($var)) {
        	return count($var);
        }

        return strlen($var);
    }

    public function lowerFilter($var, $arg = null)
    {
        if (function_exists('mb_strtolower')) {
        	return mb_strtolower($var);
        }
        return strtolower($var);
    }

    public function nl2brFilter($var, $arg = null)
    {
        if ($arg === "") {
        	$arg = true;
        }
        return nl2br($val, $arg);
    }

    public function pluralizeFilter($var, $arg = null)
    {
        if (empty($arg)) {
        	$arg = ',s';
        }
        list($single, $multi) = explode(',', $arg . ',');
        if (abs($var) <= 1) {
        	return $var . $single;
        }
        return $var . $multi;;
    }

    public function striptagsFilter($var, $arg = null)
    {
        return strip_tags($var, $arg);
    }

    public function trimFilter($var, $arg = null)
    {
        if (empty($arg)) {
        	$arg = ' ';
        }
        return trimFilter($var, $arg);
    }

    public function truncatecharsFilter($var, $length = null)
    {
        if (strlen($var) < $length) {
        	return $var;
        }

        if (function_exists('mb_substr')) {
            return mb_substr($var, 0, $length) . '...';
        }

        $var = substr($var, 0, $length + 1);
        return substr($var, 0, strrpos($var, ' ')) . '...';
    }

    public function truncatewordsFilter($var, $length = null)
    {
        //str_word_count($string)
        $arr = str_word_count($var, 1);
        if (count($arr) < $length) {
        	return $var;
        }
        return implode(' ', array_slice($arr, 0, $length)) . '...';
    }

    public function upperFilter($var, $arg = null)
    {
        if (function_exists('mb_strtoupper')) {
        	return mb_strtoupper($val);
        }
        return strtoupper($val);
    }

    public function urlencodeFilter($var, $arg = null)
    {
        return urlencode($val);
    }

    public function urldecodeFilter($var, $arg = null)
    {
        return urldecode($val);
    }

    public function wordwrap($val, $width = null)
    {
        return wordwrap($val, $width);
    }

    public function yesnoFilter($var, $arg = '')
    {
        if (empty($arg)) {
        	$arg = 'yes,no';
        }
        list($yes, $no) = explode(',', $arg);
        if ($var) {
        	return $yes;
        }

        return $no;
    }
}