<?php
/**
 * Модель
 *
 * @author		Лесных Илья <lesnykh.ilja@gmail.com>
 * @copyright	Copyright (c) 2010, Morgenshtern
 * @version		1.0.0
 */
class Application_Model_City extends Zend_Db_Table_Abstract {
    protected $_name = 'city';
	public function getCity( $city )
	{
		$rows = $this->fetchRow( 'title = ' . $this->getAdapter()->quote( $city ) );
		return $rows;
	}
}