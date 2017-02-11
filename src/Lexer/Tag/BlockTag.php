<?php
namespace Puja\Template\Lexer\Tag;

class BlockTag extends TagAbstract
{
    protected $tagNames = array('block');
    protected $errors = array(
        'missing_tag' => 'Missing {% or %}',
        'not_support_subblock' => 'Not support sub block (Block in block)',
        'missing_begin_or_endblock' => 'Missing begin or end block',
    );

    public function debug($fileName, $fileContent)
    {
        preg_match_all('/\{\%\s*([^\{\%]*?)\s*\%\}/i', $fileContent, $matches);
        

        if ($matches[0]) {
            foreach ($matches[0] as $key => $val) {
                $matches[1][$key] = $this->parser->getHashName('blocksyntax' . $key);
            }
            $fileContent = str_replace($matches[0], $matches[1], $fileContent);
        }

        if (strpos('_' . $fileContent, '{%') || strpos('_' . $fileContent, '%}')) {
            $fileContent = str_replace(
                array('{%', '%}'),
                array($this->getDebugElm('{%'), $this->getDebugElm('%}')),
                $fileContent
            );
            return $this->getDebugData(
                $this->errors['missing_tag'],
                str_replace($matches[1], $matches[0], $fileContent)
            );
        }

        $fileContent = str_replace($matches[1], $matches[0], $fileContent);
        preg_match_all('/\{\%\s*block\s?([a-z0-9\_]*?)\s?\%\}(.*?)\{\%\s?endblock\s?\1?\s?\%\}/is', $fileContent, $matches);

        if ($matches[0]) {
            foreach ($matches[0] as $key => $blockContent) {
                preg_match_all('/\{\%\s*block\s?([a-z0-9\_]*?)\s?\%\}/i', $blockContent, $blockContentMatches);
                if (count($blockContentMatches[0]) > 1) {
                    foreach ($blockContentMatches[0] as $i => $v) {
                        $blockContentMatches[1][$i] = $this->getDebugElm($v);
                    }
                    $blockContent = str_replace($blockContentMatches[0], $blockContentMatches[1], $blockContent);
                    return $this->getDebugData(
                        $this->errors['not_support_subblock'],
                        str_replace($matches[0][$key], $blockContent, $fileContent)
                    );
                } else {
                    // normal block
                    $matches[1][$key] = $this->parser->getHashName('blockcontent' . $key);
                }
            }
            $fileContent = str_replace($matches[0], $matches[1], $fileContent);
        }

        preg_match_all('/\{\%\s*(block|endblock)\s?([a-z0-9\_]*?)\s?\%\}/i', $fileContent, $invalidMatches);

        if (empty($invalidMatches[0])) {
            return null;
        }

        foreach ($invalidMatches[0] as $key => $val) {
            $invalidMatches[1][$key] = $this->getDebugElm($val); 
        }

        $fileContent = str_replace($invalidMatches[0], $invalidMatches[1], $fileContent);
        return $this->getDebugData(
            $this->errors['missing_begin_or_endblock'],
            str_replace($matches[1], $matches[0], $fileContent)
        );
        
        return null;
    }

}
