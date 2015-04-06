<?php

/** @see Morgenshtern_Char_Abstract */
require_once 'Morgenshtern/Char/Abstract.php';

/**
 * Concrete class for formatting users' nickname into FC-like style
 *
 * @category   Morgenshtern
 * @package    Morgenshtern_Char
 * @copyright  Copyright (c) 2010 Aliance spb (http://www.morgenshtern.com)
 * @license    http://www.gnu.org/copyleft/lesser.html     LGPL
 */
class Morgenshtern_Char extends Morgenshtern_Char_Abstract
{
    protected $_model = null;
    protected $_cache = null;

	/**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
		$this->_model = new Application_Model_IbfMorgenshternMembers();
		$manager = Zend_Registry::get( 'cacheManager' );
		$this->_cache = $manager->getCache( 'main' );
	}

    /**
     * Formatting user nickname into FC-like style
     *
     * @param  string|integer
	 * @throws Morgenshtern_Char_Exception if 1 parameter is empty
     * @return string
     */
    public function format( $char )
    {
		if ( is_string( $char ) ) {
			return $this->_formatByNick( (string) $char );
		} else if ( is_numeric( $char ) ) {
			return $this->_formatById( (int) $char );
		} else {
			return $char;
			throw new Morgenshtern_Char_Exception( 'Вы не указали '
			. 'обязательный аргумент, либо передали в качестве него '
			. 'недопустимое значение.' );
		}
    }

    /**
     * Formatting battle link
     *
     * @param  $battleId string
     * @param  $city     string
     * @return string
     */
    public function formatBattle( $battleId, $city )
    {
		$cityModel = new Application_Model_City;
		$cityRow = $cityModel->getCity( $city );
		$xhtml  = sprintf( ' <a href="%slogs.pl?log=%s" class="external">', $cityRow['url'], $battleId );
		$xhtml .= '<img src="http://img.combats.com/i/fighttype1.gif"';
		$xhtml .= ' alt="Бой" title="Персонаж сейчас в бою" /></a>';
		return $xhtml;
    }

    /**
     * Formatting city gerb
     *
     * @param  $city     string
     * @return string
     */
    public function formatCity( $city )
    {
		$cityModel = new Application_Model_City;
		$cityRow = $cityModel->getCity( $city );
		$xhtml  = sprintf( '<img src="http://images.morgenshtern.com/gerb/%s_small.gif"', $cityRow['gerb'] );
		$xhtml .= sprintf( ' alt="Герб города %s" title="Герб города %1$s" /> %1$s', $city );
		return $xhtml;
    }
	
	/**
     * Formatting city info img
     *
     * @param  $city     string
     * @return string
     */
    public function formatCityInfoImg( $city )
	{
		$cityModel = new Application_Model_City;
		$cityRow = $cityModel->getCity( $city );
		return $cityRow['img'];
	}

    /**
     * Formatting by nickname
     *
     * @param  string
	 * @throws Morgenshtern_Char_Exception if 1 parameter is not a string
     * @return string
     */
    protected function _formatByNick( $char )
    {
        if ( ! is_string( $char ) ) {
			throw new Morgenshtern_Char_Exception( 'Вы передали в качестве '
			. 'обязательного аргумента недопустимое значение.' );
		}
		$cacheId = $this->formatCacheId( 'format_nick_', $char );
		if ( $nick = $this->_cache->load( $cacheId ) ) {
			return $nick;
		}
		$where = 'nick = ' . $this->_model->getAdapter()->quote( $char );
		$nick = '';
		if ( null === ( $result = $this->_model->fetchRow( $where ) ) ) {
			$nick .= sprintf( '%s <a href="http://devilscity.combats.com/inf.pl?login=%1$s" ', $this->_model->convert( $char, true ) );
			$nick .= 'class="external"><img src="http://img.combats.com/i/inf.gif" width="12" height="11" ';
			$nick .= sprintf( 'alt="Информация о %s" title="Информация о %1$s" /></a>', $this->_model->convert( $char, true ) );
		} else {
			if ( ! empty( $result['clan'] ) )
			{
				$nick .= sprintf( '<a href="http://capitalcity.combats.com/encicl/klan/%s.html" '
				      . 'class="external">', $result['clan'] );
				$nick .= sprintf( '<img src="http://img.combats.com/i/klan/%s.gif" '
				      . 'alt="Информация о клане %1$s" title="Информация о клане %1$s" '
					  . 'width="24" height="15" /></a>', $result['clan'] );
			}
			if ( $result['member_id'] == 0 ) {
				$nick .= $this->_model->convert( $result['nick'] );
			} else {
				$nick .= sprintf( '<a href="http://forum.morgenshtern.com/index.php?act=Profile&amp;CODE=03&amp;MID=%d"'
					  . ' class="external">%s</a> ', $result['member_id'], $this->_model->convert( $result['nick'] ) );
			}
			$nick .= sprintf( '[%d]', $result['level'] );
			$nick .= sprintf( '<a href="http://devilscity.combats.com/inf.pl?%s" ', $result['char_id'] );
			$nick .= sprintf( 'class="external"><img src="%s" width="12" height="11" ', $this->formatCityInfoImg( $result['native_city'] ) );
			$nick .= sprintf( 'alt="Информация о %s" title="Информация о %1$s" /></a>', $this->_model->convert( $result['nick'] ) );
		}
		$this->_cache->save( $nick, $cacheId, array( 'format', 'char' ) );
		return $nick;
	}

    /**
     * Formatting by ID
     *
     * @param  integer
	 * @throws Morgenshtern_Char_Exception if 1 parameter is not an integer
     * @return string
     */
    protected function _formatById( $id )
    {
        if ( ! is_numeric( $id ) ) {
			throw new Morgenshtern_Char_Exception( 'Вы передали в качестве '
			. 'обязательного аргумента недопустимое значение.' );
		}
		$cacheId = $this->formatCacheId( 'format_id_', $id );
		if ( $nick = $this->_cache->load( $cacheId ) ) {
			return $nick;
		}
		$where = 'member_id = ' . intval( $id );
		$nick = '';
		if ( null === ( $result = $this->_model->fetchRow( $where ) ) ) {
			$nick = '<i>невидимка</i>';
		} else {
			if ( ! empty( $result['clan'] ) )
			{
				$nick .= sprintf( '<a href="http://capitalcity.combats.com/encicl/klan/%s.html" '
				      . 'class="external">', $result['clan'] );
				$nick .= sprintf( '<img src="http://img.combats.com/i/klan/%s.gif" '
				      . 'alt="Информация о клане %1$s" title="Информация о клане %1$s" '
					  . 'width="24" height="15" /></a>', $result['clan'] );
			}
			$nick .= sprintf( '<a href="http://forum.morgenshtern.com/index.php?act=Profile&amp;CODE=03&amp;MID=%d"'
			      . ' class="external">%s</a> ', $result['member_id'], $this->_model->convert( $result['nick'] ) );
			$nick .= sprintf( '[%d]', $result['level'] );
			$nick .= sprintf( '<a href="http://devilscity.combats.com/inf.pl?login=%s" ', $this->_model->convert( $result['nick'] ) );
			$nick .= sprintf( 'class="external"><img src="%s" width="12" height="11" ', $this->formatCityInfoImg( $result['native_city'] ) );
			$nick .= sprintf( 'alt="Информация о %s" title="Информация о %1$s" /></a>', $this->_model->convert( $result['nick'] ) );
		}
		$this->_cache->save( $nick, $cacheId, array( 'format', 'char' ) );
		return $nick;
	}

    protected function _recode( $value, $from = 'utf-8', $to = 'windows-1251' ) {
        return iconv( $from, $to, $value );
    }
	
	protected function _translit( $string )
	{
		$charFromMap = array(
			'А', 'Б', 'В', 'Г', 
			'Д', 'Е', 'Ё', 'Ж', 
			'З', 'И', 'Й', 'К', 
			'Л', 'М', 'Н', 'О', 
			'П', 'Р', 'С', 'Т', 
			'У', 'Ф', 'Х', 'Ц', 
			'Ч', 'Ш', 'Щ', 'Э', 
			'Ю', 'Я',
			'а', 'б', 'в', 'г', 
			'д', 'е', 'ё', 'ж', 
			'з', 'и', 'й', 'к', 
			'л', 'м', 'н', 'о', 
			'п', 'р', 'с', 'т', 
			'у', 'ф', 'х', 'ц', 
			'ч', 'ш', 'щ', 'э', 
			'ю', 'я'
		);
		$charToMap = array(
			'A', 'B', 'V', 'G', 
			'D', 'E', 'E', 'ZH', 
			'Z', 'I', 'Y', 'K', 
			'L', 'M', 'N', 'O', 
			'P', 'R', 'S', 'T', 
			'U', 'F', 'H', 'C', 
			'CH', 'SH', 'SHCH', 
			'E', 'YU', 'YA',
			'a', 'b', 'v', 'g', 
			'd', 'e', 'e', 'zh', 
			'z', 'i', 'y', 'k', 
			'l', 'm', 'n', 'o', 
			'p', 'r', 's', 't', 
			'u', 'f', 'h', 'c', 
			'ch', 'sh', 'shch', 
			'e', 'yu', 'ya'
		);
		return str_replace( $charFromMap, $charToMap, $string );
	}
	
	public function formatCacheId( $prefix = '', $id )
	{
		if ( preg_match( '#[^a-zA-Z0-9_]#', $id ) ) {
			$id = $this->_translit( $id );
			$id = preg_replace( '#[^a-zA-Z0-9_]#', '', $id );
		}
		return $prefix . $id;
	}
}