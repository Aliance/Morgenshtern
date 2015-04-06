<?php
class Morgenshtern_View_Helper_MagicHeadScript extends Zend_View_Helper_HeadScript
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
  
  public function magicHeadScript()
  {
    if (self::$combine) {
      return $this->toString();
    } else {
      return $this->view->headScript();
    }
  }
  
  public function itemToString($item, $indent, $escapeStart, $escapeEnd)
  {
    $attrString = '';
    if (!empty($item->attributes)) {
      foreach ($item->attributes as $key => $value) {
        if (!$this->arbitraryAttributesAllowed()
          && !in_array($key, $this->_optionalAttributes))
        {
          continue;
        }
        if ('defer' == $key) {
          $value = 'defer';
        }
        $attrString .= sprintf(' %s="%s"', $key, ($this->_autoEscape) ? $this->_escape($value) : $value);
      }
    }

    $type = ($this->_autoEscape) ? $this->_escape($item->type) : $item->type;
    $html = $indent . '<script type="' . $type . '"' . $attrString . '>';
    if (!empty($item->source)) {
       $html .= PHP_EOL . $indent . '  ' . $escapeStart . PHP_EOL . $item->source . $indent . '  ' . $escapeEnd . PHP_EOL . $indent;
    }
    $html .= '</script>';

    if (isset($item->attributes['conditional'])
      && !empty($item->attributes['conditional'])
      && is_string($item->attributes['conditional']))
    {
      $html = '<!--[if ' . $item->attributes['conditional'] . ']> ' . $html . '<![endif]-->';
    }

    return $html;
  }

  public function searchJsFile($src)
  {
    $path = $_SERVER['DOCUMENT_ROOT'] . $src;
    if (is_readable($path)) {
      return $path;
    }
    foreach (self::$symlinks as $virtualPath => $realPath) {
      $path = str_replace($virtualPath, $realPath, "/$src");
      if (is_readable($path)) {
        return $path;
      }
    }
    return false;
  }  
  
  public function isCachable($item)
  {
    if (isset($item->attributes['conditional'])
      && !empty($item->attributes['conditional'])
      && is_string($item->attributes['conditional']))
    {
      return false;
    }
    
    if (!empty($item->source) && false===strpos($item->source, '//@non-cache')) {
      return true;
    }
    
    if (!isset($item->attributes['src']) || !$this->searchJsFile($item->attributes['src'])) {
      return false;
    }
    return true;
  }
  
  public function cache($item)
  {
    if (!empty($item->source)) {
      $this->_cache[] = $item->source;
    } else {
      $filePath = $this->searchJsFile($item->attributes['src']);
      $this->_cache[] = array(
        'filepath' => $filePath,
        'mtime' => filemtime($filePath)
      );
    }
  }
  
  public function toString($indent = null)
  {
    $headScript = $this->view->headScript();
    
    $indent = (null !== $indent)
        ? $headScript->getWhitespace($indent)
        : $headScript->getIndent();

    if ($this->view) {
      $useCdata = $this->view->doctype()->isXhtml() ? true : false;
    } else {
      $useCdata = $headScript->useCdata ? true : false;
    }
    $escapeStart = ($useCdata) ? '//<![CDATA[' : '//<!--';
    $escapeEnd  = ($useCdata) ? '//]]>'    : '//-->';

    $items = array();
    $headScript->getContainer()->ksort();
    foreach ($headScript as $item) {
      if (!$headScript->_isValid($item)) {
        continue;
      }
      if (!$this->isCachable($item)) {
        $items[] = $this->itemToString($item, $indent, $escapeStart, $escapeEnd);
      } else {
        $this->cache($item);
      }
    }
    
    array_unshift($items, $this->itemToString($this->getCompiledItem(), $indent, $escapeStart, $escapeEnd));

    $return = implode($headScript->getSeparator(), $items);
    return $return;
  }
  
  private function getCompiledItem()
  {
    $filename = md5(serialize($this->_cache));
    $path = $_SERVER['DOCUMENT_ROOT'] . self::$cacheDir . $filename . (self::$compress? '.min' : '') . '.js';
    if (!file_exists($path)) {
      $dir = dirname($path);
	  if ( ! is_dir( $dir ) ) mkdir( $dir, 0777, true);
      $jsContent = '';
      foreach ($this->_cache as $js) {
        if (is_array($js)) {
          $jsContent .= file_get_contents($js['filepath']) . "\n\n";
        } else {
          $jsContent .= $js . "\n\n";
        }
      }
      if (self::$compress) {
        require_once( 'JSMin.php' );
		$jsContent = JSMin::minify($jsContent);
      }
      file_put_contents($path, $jsContent);
    }
    
    $url = str_replace($_SERVER['DOCUMENT_ROOT'], '', $path);
    $item = $this->createData('text/javascript', array('src'=>$url));
    return $item;
  }
}