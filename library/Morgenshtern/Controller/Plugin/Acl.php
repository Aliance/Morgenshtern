<?php
/**
 * Плагин для определения уровня прав доступа пользователя
 *
 * Выполняется до начала диспетчеризации
 *
 * @author		Лесных Илья <lesnykh.ilja@gmail.com>
 * @copyright	Copyright (c) 2010, Morgenshtern
 * @version		1.0.0
 */
final class Morgenshtern_Controller_Plugin_Acl 
    extends Zend_Controller_Plugin_Abstract 
{
	protected $_role;
	protected $_acl;
	function __construct( $role = 'guest', Zend_Acl $acl ) {
		if ( null !== $acl ) {
			$this->_acl = $acl;
		} else {
			throw new Zend_Acl_Exception( 'Не передан Acl адаптер.' );
		}
		$this->_role = $role;
	}
	public function preDispatch( Zend_Controller_Request_Abstract $request )
	{
		$resource = $request->getControllerName();
		$right    = $resource . ':' . $request->getActionName();

		if ( ! $this->_acl->has( $resource ) ) {
			$request->setModuleName( 'default' )
					->setControllerName( 'error' )
					->setActionName( 'error' )
					->setDispatched( false );
		}
		if ( ! $this->_acl->isAllowed( $this->_role, $resource, $right ) ) {
			$request->setModuleName( 'default' )
					->setControllerName( 'error' )
					->setActionName( 'access-deny' )
					->setDispatched( false );
		}
	}
}
?>