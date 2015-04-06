<?php
/**
 * Модель
 *
 * @author		Лесных Илья <lesnykh.ilja@gmail.com>
 * @copyright	Copyright (c) 2010, Morgenshtern
 * @version		1.0.0
 */
class Application_Model_Option extends Zend_Db_Table_Abstract {
    protected $_name = 'option';
	protected $_dependentTables = array( 'Application_Model_ItemOption' );
}