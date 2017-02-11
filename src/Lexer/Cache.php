<?php
namespace Puja\Template\Lexer;
use Puja\Stdlib\File;

class Cache extends LexerAbstract
{
    const CACHE_LEVEL_0 = 0; // no cache (always generate new compiled file)
    const CACHE_LEVEL_1 = 1; // ONLY generate new compiled file when template files (.tpl) were changed
    const CACHE_LEVEL_2 = 2; // ONLY generate new compiled file when user delete old compiled files

    public function check()
    {
        $fpCache = new File\Info($this->parser->getTplCachedFile());
        $fp = new File\Info($this->parser->getTplCompiledFile());
        if ($this->parser->getCacheLevel() && $fp->isFile() && $fpCache->isFile()) {
            if (self::CACHE_LEVEL_2 == $this->parser->getCacheLevel() || $this->checkMTimeFiles($fp->getMTime())) {
                $this->parser->applyCustomFilter($this->tplObj);
                $this->parser->applyCustomTag($this->tplObj);
                return true;
            }
        }

        return false;
    }

    protected function checkMTimeFiles($mTime = 0)
    {
        $tplFiles = include $this->parser->getTplCachedFile();
        $mTimes = array(0);
        foreach ($tplFiles as $filePath) {
            $file = new File\Info($filePath);
            if (!$file->isFile()) {
                return false;
            }
            $mTimes[] = $file->getMTime();

        }

        if (max($mTimes) < $mTime) {
            return true;
        }
        return false;
    }
}