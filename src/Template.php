<?php
namespace Puja\Template;
use Puja\Stdlib\Folder\Folder;
use Puja\Stdlib\File;

class Template
{
    protected $config; // store template config data
    protected $data; // store pre-define data ( update by add() && set() )

    private $tags; // store all tag objects
    private $filters; // store all filter objects

    public function __construct(array $config = array())
    {
        if (empty($config['cacheDir'])) {
            throw new Exception('$config[cacheDir] is required');
        }

        $folder = new Folder($config['cacheDir']);
        if (!$folder->isWritable()) {
            throw new Exception('CacheDir must be writable');
        }

        if (empty($config['templateDirs'])) {
            throw new Exception('$config[templateDirs] is required');
        }

        if (!is_array($config['templateDirs'])) {
            throw new Exception('TemplateDirs must be array');
        }

        if (!empty($config['customFilter']) && !class_exists($config['customFilter'])) {
            throw new Exception('Class: ' . $config['customFilter'] . ' does not exist');
        }

        if (!empty($config['customTag']) && !class_exists($config['customTag'])) {
            throw new Exception('Class: ' . $config['customTag'] . ' does not exist');
        }

        if (!array_key_exists('cacheLevel', $config)) {
            $config['cacheLevel'] = 0;
        }

        if ($config['cacheLevel'] < 0 || $config['cacheLevel'] > 2) {
            throw new Exception('Sorry, currently we only support  $config[cacheLevel] value is in [0, 1, 2]');
        }

        $this->config = $config;
        $this->data = array();

    }

    public function add($key, $values)
    {
        $this->data[$key] = $values;
    }

    public function set($data)
    {
        $this->data = array_merge($this->data, $data);
    }

    public function parse($tplFile, $data = array(), $return = false, $contentType = 'text/html; charset=utf-8')
    {
        $parser = new Parser($this, $tplFile, $data + $this->data);
        extract($data);
        $ast = include $parser->getTplCompiledFile();
        if ($return) {
            return $ast;
        }

        header('Content-Type:' . $contentType);
        echo $ast;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function addTag($key, $tag)
    {
        if (!($tag instanceof Lexer\Tag\TagAbstract)) {
            throw new Exception('Argument 1 must be instance of Puja\\Template\\Lexer\\Tag\\TagAbstract, given ' . gettype($tag));
        }

        $this->tags[$key] = $tag;
    }

    public function addFilter($key, $filter)
    {
        if (!($filter instanceof Lexer\Filter\FilterAbstract)) {
            throw new Exception('Argument 1 must be instance of Puja\\Template\\Lexer\\Filter\\FilterAbstract, given ' . gettype($filter));
        }

        $this->filters[$key] = $filter;
    }
}