<?php
/**
 * Модель прав доступа к ресурсам
 *
 * @author		Лесных Илья <lesnykh.ilja@gmail.com>
 * @copyright	Copyright (c) 2010, Morgenshtern
 * @version		1.0.0
 */
class Application_Model_RoleResource extends Zend_Db_Table_Abstract {
    protected $_name = 'role_resource';
	protected $_referenceMap    = array(
        'role' => array(
            'columns'           => 'role_resource_role_id',
            'refTableClass'     => 'Application_Model_Role',
            'refColumns'        => 'role_id'
        ),
        'resource' => array(
            'columns'           => 'role_resource_resource_id',
            'refTableClass'     => 'Application_Model_Resource',
            'refColumns'        => 'resource_id'
        )
    );
}