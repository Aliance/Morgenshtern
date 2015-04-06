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
    protected $_cache;
    protected $_auth;
    protected $_authSession;
    protected $_acl;
	protected function _initConfig() {
        if ( $this->_config === null ) {
			$config = new Zend_Config( $this->getOptions() );
			$this->_config = $config;
		}
        return $this->_config;
	}
	protected function _initMultiDBSettings() 
	{
        $this->bootstrap( 'MultiDB' );
		$multiDbResource = $this->getResource( 'MultiDB' );
		$multiDb = $multiDbResource->getDb( 'forum' );

		Zend_Registry::set( 'multiDb', $multiDb );

		return $this;
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
	protected function _initCache() 
	{
        $this->bootstrap( 'db' );
		$db = $this->getResource( 'db' );
		$this->bootstrap( 'CacheManager' );
		$manager = $this->getResource( 'CacheManager' );
		Zend_Registry::set( 'cacheManager', $manager );
		$cache = $manager->getCache( 'main' );
		$this->_cache = $cache;
		$db->cache = $cache;
		return $this->_cache;
    }
	protected function _initLocaleSetting() 
	{
		$this->bootstrap( 'Locale' );
		$locale = $this->getResource( 'Locale' );
		$this->bootstrap( 'Cache' );

		Zend_Registry::set( 'locale', $locale );
		Zend_Locale::setCache( $this->_cache );
		Zend_Locale_Format::setOptions(
			array(
				'locale'      => $locale,
				'fix_date'    => true,
				'date_format' => 'dd.MMMM.YYYY'
			)
		);

		return $this;
    }
	protected function _initDateSetting() 
	{
		Zend_Date::setOptions( array( 'format_type' => 'php' ) );
		return $this;
	}
	protected function _initTranslator() 
	{
		$this->bootstrap( 'Cache' );
		$this->bootstrap( 'Logger' );
		$logger = $this->getResource( 'Logger' );
		$this->bootstrap( 'Translate' );
		$translator = $this->getResource( 'Translate' );

		Zend_Translate::setCache( $this->_cache );
		$translator->setOptions(
			array(
				'log'             => $logger,
				'logMessage'      => "Missing '%message%' within locale '%locale%'",
				'logUntranslated' => true
			)
		);
		Zend_Validate_Abstract::setDefaultTranslator( $translator );

		return $this;
	}
	protected function _initAuth() {
		if ( $this->_auth === null ) {
			$this->_auth = Zend_Auth::getInstance();
		}
		return $this->_auth;
	}
	protected function _initViewSettings() 
	{
        $this->bootstrap( 'View' );
		$view = $this->getResource( 'View' );
		$this->bootstrap( 'Auth' );

		$view->auth = $this->_auth;
		$view->doctype( 'XHTML1_STRICT' );
		$view->headTitle()->setSeparator( ' — ' )->set( 'Официальный сайт клана Morgenshtern' );
		$view->headMeta()->setHttpEquiv( 'Content-Type', 'text/html; charset=utf-8' )
						 ->setHttpEquiv( 'Accept-Encoding', 'gzip, deflate' )
						 ->setHttpEquiv( 'X-UA-Compatible', 'IE=IE8' )
						 ->setName( 'google-site-verification', 'lB4TFlZ1Qyy51oXkFP8omZ6vkz75PpmoirsUou2KaUo' )
						 ->setName( 'yandex-verification', '53719c9a71688a0d' )
						 ->setName( 'robots', 'index,follow' )
						 ->setName( 'keywords', 'Morgenshtern, Лесных Илья, Aliance spb, Aliance, FC, combats, БК, Бойцовский Клуб, Моргенштерн, Утренняя звезда' )
						 ->setName( 'description', 'Официальный сайт клана Morgenshtern в Бойцовском Клубе' );
		$view->headLink()->setStylesheet( '/css/global.css', 'screen' )
						 #->appendStylesheet( '/css/reset.css', 'screen' )
						 ->appendStylesheet( '/css/layout.css', 'screen' )
						 ->appendStylesheet( '/css/ui.css', 'screen' )
						 ->appendStylesheet( '/css/shadowBox.css', 'screen' )
						 ->appendStylesheet( '/css/ie.layout.css', 'screen', 'IE' )
						 ->headLink( array( 'rel' => 'shortcut icon', 'href' => 'http://images.morgenshtern.com/favicon.ico' ) );
		if ( $this->_auth->hasIdentity() ) {
			$view->headLink()->appendStylesheet( '/css/auth.css', 'screen' );
		} else {
			$view->headLink()->appendStylesheet( '/css/non-auth.css', 'screen' );
		}
        $view->headScript()->setFile( 'http://yandex.st/jquery/1.4.2/jquery.min.js', 'text/javascript' )
                         ->appendFile( 'http://www.morgenshtern.com/js/jquery.floatbox.js', 'text/javascript' )
                         ->appendFile( '/js/scripts.js', 'text/javascript' )
                         ->appendFile( '/js/shadowBox.js', 'text/javascript' )
                         ->appendFile( '/js/pngfix.js', 'text/javascript', array( 'conditional' => 'IE' ) );

		Morgenshtern_View_Helper_MagicHeadScript::setConfig( '/js/cache', 1, 1 );
		Morgenshtern_View_Helper_MagicHeadLink::setConfig( '/css/cache', 1, 1 );

		return $this;
	}
	protected function _initAuthSession() {
        $this->bootstrap( 'Auth' );

		if ( $this->_authSession === null ) {
			$session = new Zend_Session_Namespace( 'Zend_Auth' );
			if ( $this->_auth->hasIdentity() && $session->storage->id ) {
				// 60*60*24*7
				Zend_Session::rememberMe( 604800 );
			} else {
				$this->_auth->clearIdentity();
				Zend_Session::forgetMe();
			}
			Zend_Registry::set( 'session', $session );
			$this->_authSession = $session;
		}
        return $this->_authSession;
    }
	protected function _initAcl() {
		$this->bootstrap( 'db' );
		$this->bootstrap( 'FrontController' );
		$frontController = $this->getResource( 'FrontController' );
		$this->bootstrap( 'AuthSession' );

		if ( $this->_acl === null ) {
			$acl = new Zend_Acl();
			$resource_model = new Application_Model_Resource();
			$_resources = array();
			if ( null !== ( $resources = $resource_model->fetchAll() ) ) {
				foreach ( $resources as $resource ) {
					$_resources[ $resource['resource_id'] ] = array( 'controller' => $resource['resource_controller'], 'action' => null );
					if ( empty( $resource['resource_action'] ) ) {
						if ( $acl->has( $resource['resource_controller'] ) ) {
							continue;
						}
						$acl->add( new Zend_Acl_Resource( $resource['resource_controller'] ) );
					} else {
						$_resources[ $resource['resource_id'] ]['action'] = $_resources[ $resource['resource_id'] ]['controller'] . ':' . $resource['resource_action'];
						$acl->add( new Zend_Acl_Resource( $_resources[ $resource['resource_id'] ]['action'] ), $resource['resource_controller'] );
					}
				}
			}
			$role_model = new Application_Model_Role();
			if ( null !== ( $roles = $role_model->fetchAll() ) ) {
				foreach ( $roles as $row ) {
					$parent = empty( $row['role_parent'] ) ? null : $row['role_parent'];
					$role = new Zend_Acl_Role( $row['role_value'] );
					$acl->addRole( $role, $parent );
					if ( null !== ( $_role = $row->findDependentRowset( 'Application_Model_RoleResource', 'role' ) ) ) {
						foreach ( $_role as $_row ) {
							$_resource = $_resources[ $_row['role_resource_resource_id'] ];
							if ( $_row['role_resource_allow'] == 1 ) {
								$acl->allow( $role, $_resource['controller'], $_resource['action'] );
							} else {
								$acl->deny( $role, $_resource['controller'], $_resource['action'] );
							}
						}
					}
				}
			}

			$user_role = $this->_auth->hasIdentity() ? $this->_authSession->storage->user_role : 'guest';
			$acl_plugin = new Morgenshtern_Controller_Plugin_Acl( $user_role, $acl );
			$frontController->registerPlugin( $acl_plugin );
			Zend_View_Helper_Navigation_HelperAbstract::setDefaultAcl( $acl );
			Zend_View_Helper_Navigation_HelperAbstract::setDefaultRole( $user_role );

			$this->_acl = $acl;
		}
		return $this->_acl;
	}
}