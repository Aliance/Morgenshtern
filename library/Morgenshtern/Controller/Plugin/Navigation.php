<?php
/**
 * Плагин для регистрации навигации
 *
 * Выполняется после окончания маршрутизации
 *
 * @author		Лесных Илья <lesnykh.ilja@gmail.com>
 * @copyright	Copyright (c) 2010, Morgenshtern
 * @version		1.0.0
 */
final class Morgenshtern_Controller_Plugin_Navigation 
    extends Zend_Controller_Plugin_Abstract
{
	protected $_view;
	
	public function routeShutdown( Zend_Controller_Request_Abstract $request )
	{
		$bootstrap = Zend_Controller_Front::getInstance()->getParam( 'bootstrap' );
		$acl = $bootstrap->getResource( 'Acl' );
		$this->_view = $bootstrap->getResource( 'View' );
		$container = new Zend_Navigation();
		$this->_view->navigationContainer = $container;
		$pages = array(
			array(
				'label'			=>	'Главная',
				'module'		=>	'default',
				'controller'	=>	'index',
				'action'		=>	'index',
				'route'			=>	'default',
				'pages'			=>	array()
			)
		);
		$container->setPages( $pages );

		$this->_view->navigation( $container )->breadcrumbs()
											  ->setLinkLast( false )
											  ->setMinDepth( 1 )
											  ->setMaxDepth( 15	 )
											  ->setSeparator( ' &rarr;' . PHP_EOL );
	}
}
?>