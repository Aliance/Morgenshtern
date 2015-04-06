<?php
/**
 * Модель
 *
 * @author		Лесных Илья <lesnykh.ilja@gmail.com>
 * @copyright	Copyright (c) 2010, Morgenshtern
 * @version		1.0.0
 */
class Application_Model_ItemModifier extends Zend_Db_Table_Abstract {
    protected $_name = 'item_modifier';
	protected $_referenceMap    = array(
        'item' => array(
            'columns'           => 'item_modifier_item_id',
            'refTableClass'     => 'Application_Model_Item',
            'refColumns'        => 'item_id'
        ),
        'modifier' => array(
            'columns'           => 'item_modifier_modifier_id',
            'refTableClass'     => 'Application_Model_Modifier',
            'refColumns'        => 'modifier_id'
        )
    );
}