<?php
/**
 * @license Public domain
 */
class Morgenshtern_View_Helper_MagicHeadLink extends Zend_View_Helper_HeadLink
{
    private static $cacheDir;
    private static $combine = 1;
    private static $compress = 1;
    private static $symlinks = array();

    private $_cache = array();
    
    static public function setConfig($dir, $combine = 1, $compress = 1, $symlinks = array())
    {
        self::$cacheDir = rtrim($dir, '/') . '/';
        self::$symlinks = $symlinks;
        self::$combine = $combine;
        self::$compress = $compress;
    }
    
    public function magicHeadLink()
    {
        if (self::$combine) {
            return $this->toString();
        } else {
            return $this->view->headLink();
        }
    }
    
    public function itemToString(stdClass $item)
    {
        $attributes = (array) $item;
        $link       = '<link ';

        foreach ($this->_itemKeys as $itemKey) {
            if (isset($attributes[$itemKey])) {
                if(is_array($attributes[$itemKey])) {
                    foreach($attributes[$itemKey] as $key => $value) {
                        $link .= sprintf('%s="%s" ', $key, ($this->_autoEscape) ? $this->_escape($value) : $value);
                    }
                } else {
                    $link .= sprintf('%s="%s" ', $itemKey, ($this->_autoEscape) ? $this->_escape($attributes[$itemKey]) : $attributes[$itemKey]);
                }
            }
        }

        if ($this->view instanceof Zend_View_Abstract) {
            $link .= ($this->view->doctype()->isXhtml()) ? '/>' : '>';
        } else {
            $link .= '/>';
        }

        if (($link == '<link />') || ($link == '<link >')) {
            return '';
        }

        if (isset($attributes['conditionalStylesheet'])
            && !empty($attributes['conditionalStylesheet'])
            && is_string($attributes['conditionalStylesheet']))
        {
            $link = '<!--[if ' . $attributes['conditionalStylesheet'] . ']> ' . $link . '<![endif]-->';
        }

        return $link;
    }

    public function isCachable($item)
    {
        $attributes = (array) $item;
        if (isset($attributes['conditionalStylesheet'])
            && !empty($attributes['conditionalStylesheet'])
            && is_string($attributes['conditionalStylesheet']))
        {
            return false;
        }
        if (!isset($attributes['href']) || !is_readable($_SERVER['DOCUMENT_ROOT'] . $attributes['href'])) {
            return false;
        }
        return true;
    }
    
    public function cache($item)
    {
        $attributes = (array) $item;
        $filePath = $_SERVER['DOCUMENT_ROOT'] . $attributes['href'];
        $this->_cache[] = array(
            'filepath' => $filePath,
            'mtime' => filemtime($filePath)
        );
        
    }
    
    public function toString($indent = null)
    {
        $headLink = $this->view->headLink();
        
        $indent = (null !== $indent)
                ? $headLink->getWhitespace($indent)
                : $headLink->getIndent();

        $items = array();
        $headLink->getContainer()->ksort();
        foreach ($headLink as $item) {
            if (!$headLink->_isValid($item)) {
                continue;
            }
            if (!$this->isCachable($item)) {
                $items[] = $this->itemToString($item);
            } else {
                $this->cache($item);
            }
        }
        
        array_unshift($items, $this->itemToString($this->getCompiledItem()));

        $return = implode($headLink->getSeparator(), $items);
        return $return;
    }
    
    private function getCompiledItem()
    {
        $filename = md5(serialize($this->_cache));
        $path = $_SERVER['DOCUMENT_ROOT'] . self::$cacheDir . $filename . (self::$compress? '.min' : '') . '.css';
        if (!file_exists($path)) {
            $dir = dirname($path);
			if ( ! is_dir( $dir ) ) mkdir($dir, 0777, true);
            $cssContent = '';
            foreach ($this->_cache as $css) {
                $content = file_get_contents($css['filepath']);
                if (self::$compress) {
                    #require_once( 'Minify/CSS.php' );
					$cssContent .= Minify_CSS::minify(
                        $content, 
                        array(
                            'prependRelativePath' => dirname($path),
                            'currentDir' => dirname($css['filepath']),
                            'symlinks' => self::$symlinks
                        )
                    );
                } else {
                    #require_once( 'Minify/CSS/UriRewriter.php' );
					$cssContent .= Minify_CSS_UriRewriter::rewrite(
                        $content, 
                        dirname($css['filepath']),
                        $_SERVER['DOCUMENT_ROOT'],
                        self::$symlinks
                    );
                }
                $cssContent .= "\n\n";
            }
            file_put_contents($path, $cssContent);

        }
        
        $url = str_replace($_SERVER['DOCUMENT_ROOT'], '', $path);
        $item = $this->createDataStylesheet(array('href'=>$url));
        return $item;
    }
}