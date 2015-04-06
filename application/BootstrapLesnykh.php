<?php
/**
 * Файл-загрузчик
 *
 * @author		Лесных Илья <lesnykh.ilja@gmail.com>
 * @copyright	Copyright (c) 2010, Morgenshtern
 * @version		0.0.1
 */
final class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected $_config;
	protected $_autoLoader;
	protected function _initConfig() {
        if ( $this->_config === null ) {
			$config = new Zend_Config( $this->getOptions() );
			$this->_config = $config;
		}
        return $this->_config;
	}
	protected function _initAutoload()
	{
		if ( $this->_autoLoader === null ) {
			$autoLoader = new Zend_Loader_Autoloader_Resource( array( 'namespace' => '', 'basePath' => APPLICATION_PATH ) );
			$autoLoader->addResourceType( 'model', 'models/', 'Application_Model' );
			$this->_autoLoader = $autoLoader;
		}
		return $this->_autoLoader;
	}
	protected function _initPlugins()
	{
        $this->bootstrap( 'FrontController' );
        $frontController = $this->getResource( 'FrontController' );

        $error = new Zend_Controller_Plugin_ErrorHandler();
        $error->setErrorHandlerModule( 'default' )
              ->setErrorHandlerController( 'error' )
              ->setErrorHandlerAction( 'error' );
        $frontController->registerPlugin( $error );

        return $this;
    }
	protected function _initViewSettings() 
	{
        $this->bootstrap( 'View' );
		$view = $this->getResource( 'View' );
		$view->doctype('HTML5');
		$view->headTitle()->setSeparator(' — ')->set('Персональный сайт Лесных Ильи');
		$view->headMeta()->setHttpEquiv( 'X-UA-Compatible', 'IE=edge' )
						 //->setName( 'google-site-verification', '' )
						 ->setName( 'yandex-verification', '52ed930cfc6ddc00' )
						 ->setName( 'viewport', 'width=device-width, initial-scale=1' )
						 ->setName( 'robots', 'index,follow' )
						 ->setName( 'author', 'Лесных Илья' )
						 ->setName( 'description', 'Персональный сайт Лесных Ильи' );
		
		$view->headLink()->headLink( array( 'rel' => 'shortcut icon', 'href' => '//static.lesnykh.ru/favicon.ico' ) );
		$view->headLink()->appendStylesheet( '//static.lesnykh.ru/css/bootstrap.css', 'screen' )
						 ->appendStylesheet( 'https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js', 'screen', 'lt IE 9' )
						 ->appendStylesheet( 'https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js', 'screen', 'lt IE 9' );
        $view->headScript()->setFile( 'https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js', 'text/javascript' )
                         ->appendFile( '//static.lesnykh.ru/js/bootstrap.js', 'text/javascript' );

		return $this;
	}
}
