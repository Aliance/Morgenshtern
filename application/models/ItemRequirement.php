<?php
/**
 * Модель
 *
 * @author		Лесных Илья <lesnykh.ilja@gmail.com>
 * @copyright	Copyright (c) 2010, Morgenshtern
 * @version		1.0.0
 */
class Application_Model_ItemRequirement extends Zend_Db_Table_Abstract {
    protected $_name = 'item_requirement';
	protected $_referenceMap    = array(
        'item' => array(
            'columns'           => 'item_requirement_item_id',
            'refTableClass'     => 'Application_Model_Item',
            'refColumns'        => 'item_id'
        ),
        'requirement' => array(
            'columns'           => 'item_requirement_requirement_id',
            'refTableClass'     => 'Application_Model_Requirement',
            'refColumns'        => 'requirement_id'
        )
    );
}