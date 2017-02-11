User's guide
========

```php
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
```
<pre>
&lt;?php
  
  class Puja{
    /**
	 *  Folder includes template files 
	 *  @var string
	 *  */
	<strong>var $template_dir = 'templates/';</strong>
	/**
	 * Folder includes compiled files
	 * @var string
	 */
	<strong>var $cache_dir;</strong>
	/**
	 * Cache level
	 * 0: Default level. No cache
	 * 1: AUTO update each user modify template. REQUIRE configure $cache_dir
	 * 2: NOT update each user modify template, only update when user delete cached file manualy. REQUIRE configure $cache_dir.
	 * @var int
	 */
	<strong>var $cache_level;</strong>
	/**
	 * Type of template compile.
	 * - eval: call eval to compile AST. 
	 * - include: Default value. Create a PHP file from AST and then include it. REQUIRE configure $cache_dir.
	 * @var string
	 */
	<strong>var $parse_executer = 'include';</strong>
	/**
	 * Custom filter class
	 * @var Class object
	 */
	<strong>var $custom_filter;</strong>
	/**
	 * Custom tags class
	 * @var Class object
	 */
	<strong>var $custom_tags;</strong>
	/**
	 * Mode debug
	 * - if mode debug = true, enable validate template's syntax [DEVELOP]
	 * - if mode debug = false, disable validate template's syntax, [PRODUCTION]
	 * @var Boolean
	 */
	<strong>var $debug = false;</strong>
	/**
	 * Set common values for template before template parse.
	 * @var Array
	 */
	<strong>var $headers = array();</strong>
	/**
	 * Consider data is only array, not include object.
	 * - if true: Puja don't run object_to_array converter (save time )
	 * - if false: Puja run object_to_array converter.
	 * @var Boolean
	 */
	<strong>var $data_only_array  = false;</strong>
	/**
	 * Consider include multi level.
	 * true: Default value. Allow include multi level.
	 * false: only include 1 level. This option will make faster.
	 * */
	<strong>var $include_multi_level = true;</strong>
	/**
	 * Consider include multi extends.
	 * true: Default value. Allow extends multi level..
	 * false: only include 1 level. This option will make faster.
	 * */
	<strong>var $extends_multi_level = true;</strong>
    
    	/**
	 * Parse template 
	 * @param string $template_file
	 * @param array $data
	 * @param boonlean $return_value, display to browswer if $return_value = false, else return template string.
	 */
    	<strong>function parse($template_file,$data=array(),$return_value=false)</strong>{}
  }
  
</pre>
