<?php
/**
 * Модель
 *
 * @author		Лесных Илья <lesnykh.ilja@gmail.com>
 * @copyright	Copyright (c) 2010, Morgenshtern
 * @version		1.0.0
 */
class Application_Model_ItemOption extends Zend_Db_Table_Abstract {
    protected $_name = 'item_option';
	protected $_referenceMap    = array(
        'item' => array(
            'columns'           => 'item_option_item_id',
            'refTableClass'     => 'Application_Model_Item',
            'refColumns'        => 'item_id'
        ),
        'option' => array(
            'columns'           => 'item_option_option_id',
            'refTableClass'     => 'Application_Model_Option',
            'refColumns'        => 'option_id'
        )
    );
}