<?php
/**
 * Модель
 *
 * @author		Лесных Илья <lesnykh.ilja@gmail.com>
 * @copyright	Copyright (c) 2010, Morgenshtern
 * @version		1.0.0
 */
class Application_Model_Modifier extends Zend_Db_Table_Abstract {
    protected $_name = 'modifier';
	protected $_dependentTables = array( 'Application_Model_ItemModifier' );
}