<?php
namespace Puja\Template;
use Puja\Stdlib\File;

class Parser
{

    protected $tplContent;
    protected $tplRealPaths;
    protected $tplCompiledFile;
    protected $tplCachedFile;
    protected $data;

    protected $lexers;
    protected $templateDirs;
    protected $hashSeparator;
    protected $hashName;
    protected $astVar;
    protected $astHeaders;
    protected $customTag;
    protected $customFilter;
    protected $cacheDir;
    protected $cacheLevel;
    protected $isDebug;
    protected $debug;


    public function __construct(\Puja\Template\Template $tplObj, $tplFile, $data = array(), $return = false)
    {
        $this->customFilter = array();
        $this->customTag = array();
        $this->applyTplConfig($tplObj->getConfig());
        $this->tplCompiledFile = $this->cacheDir . str_replace('/', '__', $tplFile) . '.php';
        $this->tplCachedFile = $this->cacheDir . str_replace('/', '__', $tplFile) . '.cache.php';

        $cache = new Lexer\Cache($tplObj, $this);
        if ($cache->check()) {
            return;
        }

        $this->addAstHeader('<?php');
        $this->tplMTime = 0;
        $this->hashName = md5(__CLASS__ . 'HASHNAME' . rand(100, 1000));
        $this->hashSeparator = md5(__FILE__ . __CLASS__ . 'HASHSEPARATOR' . rand(100, 1000));
        $this->astVar = '$ast' . $this->hashName;
        $this->data = $data;
        $this->lexers['static'] = array(
            'special' => new Lexer\Variable\Special($tplObj, $this),
            'comment' => new Lexer\Tag\CommentTag($tplObj, $this),
        );

        $this->lexers['instances'] = array();
        $this->lexers['init'][] = 'Puja\\Template\\Lexer\\Tag\\BlockTag';
        $this->lexers['init'][] = 'Puja\\Template\\Lexer\\Tag\\ExtendsTag';
        $this->lexers['init'][] = 'Puja\\Template\\Lexer\\Tag\\IncludeTag';
        $this->lexers['init'][] = 'Puja\\Template\\Lexer\\Tag\\SetTag';
        $this->lexers['init'][] = 'Puja\\Template\\Lexer\\Variable\\Variable';
        $this->lexers['init'][] = 'Puja\\Template\\Lexer\\Tag\\GetFileTag';
        $this->lexers['init'][] = 'Puja\\Template\\Lexer\\Filter\\CCompile';
        $this->lexers['init'][] = 'Puja\\Template\\Lexer\\Tag\\CCompile';
        $this->lexers['init'][] = 'Puja\\Template\\Lexer\\Tag\\ForTag';
        $this->lexers['init'][] = 'Puja\\Template\\Lexer\\Tag\\IfTag';

        $this->debug = new Debug\Debug($tplObj, $this, $this->isDebug);
        $this->tplContent = $this->getFileContent($tplFile);

        $this->applyCustomFilter($tplObj); // must run before init class
        $this->applyCustomTag($tplObj); // must run before init class

        $builtInTags = array();
        foreach ($this->lexers['init'] as $key => $lexerName) {
            /**
            * @var \Puja\Template\Lexer\LexerAbstract
            */
            $lexer = new $lexerName($tplObj, $this);
            if (!empty($this->customTag) && $lexer instanceof Lexer\Tag\TagAbstract) {
                $builtInTags = $builtInTags + $lexer->getTagNames();
            }
            $this->lexers['instances'][$lexerName] = $lexer;
        }

        $this->checkDuplicateTag($builtInTags);
        // compile
        foreach ($this->lexers['instances'] as $lexerName => $lexer) {
            $content = $lexer->compile();
            if ($content) {
                $this->setTplContent($content);
            }
        }

        //after compile
        foreach ($this->lexers['instances'] as $lexerName => $lexer) {
            $content = $lexer->afterCompile();
            if ($content) {
                $this->setTplContent($content);
            }
        }

        $fpCache = new File\Info($this->tplCachedFile);
        $fpCache->openFile('w')->fwrite('<?php return ' . var_export($this->tplRealPaths, true). ';');
        $fp = new File\Info($this->tplCompiledFile);
        $fp->openFile('w')->fwrite(implode(PHP_EOL, $this->astHeaders) . $this->getAstVar(false) . '=\'' . $this->tplContent . '\';return ' . $this->getAstVar(false) . ';');
    }

    public function getFileContent($tplFile, $callDebug = true)
    {
        foreach ($this->templateDirs as $tplDir) {
            $file = new File\Info(rtrim($tplDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $tplFile);
            if ($file->isFile()) {
                $content = $file->getContent();
                //$content = str_replace('\'', '\\\'', $content);
                $content = addcslashes($content, '\'');
                $this->debug->beforeDebug($tplFile, $content);
                $this->tplRealPaths[] = $file->getRealPath();
                foreach ($this->lexers['static'] as $lexer) {
                    $content = $lexer->compile($content);
                }
                if ($callDebug) {
                    $this->debug->debug($tplFile, $content);
                }
                
                return $content;
            }
        }

        throw new Exception(sprintf('File %s is not found in [%s]', $tplFile, implode(',', $this->templateDirs)));
    }

    public function getTplCachedFile()
    {
        return $this->tplCachedFile;
    }

    public function getHashSeparator()
    {
        return $this->hashSeparator;
    }

    public function getHashName($name)
    {
        return $this->hashSeparator . $this->hashName . $name . $this->hashSeparator;
    }

    public function isHashName($hashName)
    {
        if (substr($hashName, 0, strlen($this->hashName)) === $this->hashName) {
            return true;
        }

        return false;
    }
    public function convertHashName($hashName)
    {
        return substr($hashName, strlen($this->hashName));
    }

    public function getTplContent()
    {
        return $this->tplContent;
    }

    public function setTplContent($tplContent)
    {
        $this->tplContent = $tplContent;
    }


    public function getAstVar($fullOption = true)
    {
        if (empty($fullOption)) {
            return $this->astVar;
        }

        return $this->astVar . '.=\'';

    }

    public function buildVariableName($variables = array(), $checkIsset = true, $checkArrayObj = false)
    {
        return $this->lexers['instances']['Puja\\Template\\Lexer\\Variable\\Variable']->buildVariableName($variables, $checkIsset, $checkArrayObj);
    }

    public function getTplCompiledFile()
    {
        return $this->tplCompiledFile;
    }

    public function getTplMTime()
    {
        return $this->tplMTime;
    }

    public function getCacheLevel()
    {
        return $this->cacheLevel;
    }

    public function getCacheDir()
    {
        return $this->cacheDir;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getCustomTag()
    {
        return $this->customTag;
    }

    public function getCustomFilter()
    {
        return $this->customFilter;
    }

    public function getAst()
    {
        return $this->ast;
    }

    public function getIsDebug()
    {
        return $this->isDebug;
    }

    public function addAstHeader($astHeader)
    {
        if (empty($this->astHeaders)) {
            $this->astHeaders = array();
        }
        $this->astHeaders[] = $astHeader;
    }

    public function applyCustomFilter(\Puja\Template\Template $tplObj)
    {
        $builtinFilter = new Lexer\Filter\Builtin($tplObj, $this);
        $tplObj->addFilter($builtinFilter->getClassName(), $builtinFilter);
        $this->customFilter = $builtinFilter->getFilterNames();

        $tplConfig = $tplObj->getConfig();
        if (!empty($tplConfig['customFilter'])) {
            $clsName = $tplConfig['customFilter'];
            if (!class_exists($clsName)) {
                throw new Exception('Class: ' . $clsName . ' does not exist');
            }

            $filter = new $clsName($tplObj, $this);
            $tplObj->addFilter($filter->getClassName(), $filter);
            $this->customFilter = $this->customFilter + $filter->getFilterNames();
        }

    }

    public function applyCustomTag(\Puja\Template\Template $tplObj)
    {
        $tplConfig = $tplObj->getConfig();
        if (!empty($tplConfig['customTag'])) {
            $clsName = $tplConfig['customTag'];
            if (!class_exists($clsName)) {
                throw new Exception('Class: ' . $clsName . ' does not exist');
            }

            $tag = new $clsName($tplObj, $this);
            $tplObj->addTag($tag->getClassName(), $tag);
            $this->customTag = $this->customTag + $tag->getTagNames();
        }
    }

    public function getLexers()
    {
        return $this->lexers;
    }

    protected function checkDuplicateTag($builtInTags)
    {
        $duplicatedTags = array_intersect_key($builtInTags, $this->customTag);
        if (!empty($duplicatedTags)) {
            throw new Exception('Tags: *' . implode('*, *', array_keys($duplicatedTags)) . '* are already taken!');
        }
    }

    protected function applyTplConfig($config)
    {
        $this->cacheDir = rtrim($config['cacheDir'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->cacheLevel = $config['cacheLevel'];;
        $this->templateDirs = $config['templateDirs'];
        krsort($this->templateDirs);

        $this->isDebug = !empty($config['debug']);
    }

    
    
}