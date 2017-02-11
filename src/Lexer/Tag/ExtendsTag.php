<?php
namespace Puja\Template\Lexer\Tag;

class ExtendsTag extends TagAbstract
{
    protected $tagNames = array('extends');

    protected function init()
    {
        $this->errors = array_merge($this->errors, array(
            'not_support_multi_extends' => 'Sorry, currently we dont support multi extends',
            'not_allow_space_in_filename' => 'Not allow space in file name',
        ));
        
        $content = $this->parser->getTplContent();
        preg_match('/\{\%\s*extends\s+(.*?)\s*\%\}/is', $content, $matches);
        if (empty($matches)) {
            $this->parser->setTplContent($this->removeRemains($content));
            return;
        }

        preg_match_all('/\{\%\s*block\s?([a-z0-9\_]*?)\s?\%\}(.*?)\{\%\s?endblock\s?\1?\s?\%\}/is', $content, $blocks);

        // master content
        $content = $this->parser->getFileContent($matches[1]);
        // if current file dont have any block, just keep content from master
        if (empty($blocks[1])) {
            $this->parser->setTplContent($this->removeRemains($content));
            return;
        }

        preg_match_all('/\{\%\s*block\s?([a-z0-9\_]*?)\s?\%\}(.*?)\{\%\s?endblock\s?\1?\s?\%\}/is', $content, $masterBlocks);
        // if master dont have any block, just clear all block from current file (mean return master content)
        if (empty($masterBlocks[1])) {
            $this->parser->setTplContent($this->removeRemains($content));
            return;
        }

        $blockNames = array_flip($blocks[1]);
        foreach ($masterBlocks[1] as $key => $blockName) {
            if (array_key_exists($blockName, $blockNames)) {
                $blockContent = $blocks[2][$blockNames[$blockName]];
                // apply {{ block.supper }}
                $blockContent = preg_replace('/\{\{\s*block\.supper\s*\}\}/i', $masterBlocks[2][$key], $blockContent);
                $masterBlocks[2][$key] = $blockContent;
            }
        }

        $this->parser->setTplContent(str_replace($masterBlocks[0], $masterBlocks[2], $content));
        return;
    }

    private function removeRemains($content)
    {
        $content = preg_replace('/\{\%\s?block\s?(.*?)\s?\%\}/i', '', $content);
        $content = preg_replace('/\{\%\s?endblock\s?(.*?)\s?\%\}/i', '', $content);
        $content = preg_replace('/\{\%\s?endblock\s?\%\}/i', '', $content);
        return $content;
    }

    public function debug($fileName, $fileContent)
    {
        $error = null;
        $search = null;
        preg_match_all('/\{\%\s*extends\s+(.*?)\s*\%\}/is', $fileContent, $matches);

        if (empty($matches[0])) {
            return;
        }

        if (1 < count($matches[0])) {
            $error = $this->errors['not_support_multi_extends'];
        } else if (strpos(trim($matches[1][0]), ' ')) {
            $error = $this->errors['not_allow_space_in_filename'];
        }

        if (!empty($error)) {
            foreach ($matches[0] as $key => $val) {
            $matches[1][$key] = $this->getDebugElm($val);
            }
            
            return $this->getDebugData(
                $error,
                str_replace($matches[0], $matches[1], $fileContent)
            );
        }

        $debugFileExists = $this->debugFileExists($matches, $fileContent);
        if ($debugFileExists) {
            return $debugFileExists;
        }
    }

    
    
}