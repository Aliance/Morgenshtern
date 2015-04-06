<?php

class IndexController extends Zend_Controller_Action 
{
    protected $_bootstrap = null;
    protected $_cache = null;
    protected $_multiDb = null;

	function init()
    {
        $this->_bootstrap = $this->getInvokeArg( 'bootstrap' );
        $manager = $this->_bootstrap->getResource( 'CacheManager' );
		$this->_cache = $manager->getCache( 'block' );
		$multiDbResource = $this->_bootstrap->getResource( 'MultiDB' );
		$this->_multiDb = $multiDbResource->getDb( 'forum' );

		$this->getResponse()->setHeader( 'Content-Type', 'text/html; charset=utf-8' );
    }
    public function indexAction() 
    {
        $this->view->headLink()->appendStylesheet( '/css/index.index.css', 'screen' );
        $this->view->headLink()->appendStylesheet( '/css/forum.css', 'screen' );
        $this->view->headLink()->appendStylesheet( '/css/paginator.css', 'screen' );
        $this->view->headScript()->appendFile( '/js/paginator.js', 'text/javascript' );
		$this->view->headScript()->appendFile( '/js/expand.js', 'text/javascript' );
		$this->view->headTitle()->prepend( 'Новости' );

        $request = $this->getRequest();
        $page = $request->getParam( 'page' );
		$cat = $request->get( 'cat' );

		$topicsModel = new Application_Model_IbfTopics();
        $topics = $topicsModel->getTopicList( $cat, $page );
        $this->view->topics = $topics;
		
		$this->view->headTitle()->prepend( $topics->rows[0]['title'] );
    }
    public function charterAction() 
    {
		$this->view->headLink()->appendStylesheet( '/css/index.charter.css', 'screen' );
		$this->view->headLink()->appendStylesheet( '/css/global.messages.css', 'screen' );
		$this->view->headScript()->appendFile( '/js/charter.js', 'text/javascript' );
		$this->view->headTitle()->prepend( 'Устав братства' );
    }
	public function staffSort( $a, $b )
	{
		if ( $a['online'] == 1 AND $a['online'] == 0 ) {
			return 0;
		}
		return ( $a['online'] == 1 ) ? -1 : 1;
	}
    public function staffAction() 
    {
		$this->view->headLink()->appendStylesheet( '/css/index.staff.css', 'screen' );
		$this->view->headLink()->appendStylesheet( '/css/global.table.css', 'screen' );
		$this->view->headScript()->appendFile( '/js/index.staff.js', 'text/javascript' );
		$this->view->headTitle()->prepend( 'Состав братства' );

        $staffModel = new Application_Model_IbfMorgenshternMembers;
		$staff = $staffModel->getStaff();
		usort( $staff, array( &$this, 'staffSort' ) );
		$this->view->staff = $staff;
    }
	public function historyAction() 
    {
		$this->view->headLink()->appendStylesheet( '/css/index.history.css', 'screen' );
		$this->view->headTitle()->prepend( 'История братства' );
    }
	public function diplomacyAction() 
    {
		$this->view->headTitle()->prepend( 'Дипломатия' );
		$diplomacyModel = new Application_Model_Diplomacy();
		$this->view->row = $diplomacyModel->getAllies();
    }
	public function unionAction() 
	{
		$this->view->headTitle()->prepend( 'Дипломатия' );
		$this->view->headTitle()->prepend( 'Проект союзного договора' );
		$char = new Morgenshtern_Char();
		$this->view->chief = $char->format( 'Nib' );
	}
	public function itAction() 
    {
		$this->view->headTitle()->prepend( 'Дипломатия' );
		$this->view->char = new Morgenshtern_Char();
    }
	public function addNewsAction() 
    {
		$this->getResponse()->setRedirect( 'http://forum.morgenshtern.com/index.php?showforum=23', 303 );
    }
	public function vacancyAction() 
    {
		$this->getResponse()->setRedirect( 'http://forum.morgenshtern.com/index.php?showforum=3', 303 );
    }
	public function jeremiadAction() 
    {
		$this->getResponse()->setRedirect( 'http://forum.morgenshtern.com/index.php?showforum=5', 303 );
    }
	/**
	 * Получение Адаптера для Авторизации
	 *
	 * Авторизируем пользователя на основе введенного адреса электронной почты и md5-хеша пароля
	 *
	 * @access		protected
	 * @author		Лесных Илья <lesnykh.ilja@gmail.com>
	 * @copyright	Copyright (c) 2010, Webdoka
	 * @version		1.0.0
	 *
	 * @param		string		$email			Адрес электронной почты
	 * @param		string		$userPassword	Пароль в чистом виде
	 *
	 * @return		resource	Адаптер для авторизации
	 */
	protected function _getAuthAdapter( $nick, $userPassword ) {
		$authAdapter = new Zend_Auth_Adapter_DbTable( $this->_multiDb, 'ibf_users', 'name', 'converge_pass_hash', 'MD5( CONCAT( MD5( converge_pass_salt ), MD5(?) ) )' );
		$authAdapter->setIdentity( $nick )
					->setCredential( $userPassword );

		return $authAdapter;
	}
    public function loginAction() 
    {
        $auth = $this->_bootstrap->getResource( 'Auth' );
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout->disableLayout();
		
		if ( $auth->hasIdentity() ) {
			return $this->_redirect( '/' );
		}
		if ( ! $this->getRequest()->isPost() ) {
			return $this->_redirect( '/' );
		}
		
		$nick = trim( $this->getRequest()->getPost( 'nick' ) );
		$password = trim( $this->getRequest()->getPost( 'password' ) );
		
		$authAdapter = $this->_getAuthAdapter( $nick, $password );
		$result = $auth->authenticate( $authAdapter );
		if ( $result->isValid() ) {
			$currentUser = $authAdapter->getResultRowObject( null, array( 'converge_pass_hash', 'converge_pass_salt' ) );
			$currentUser->user_role = $this->getGroup( $currentUser->mgroup );
			$auth->getStorage()->write( $currentUser );
		}
		return $this->_redirect( '/' );
    }
    public function logoutAction() 
    {
        $auth = $this->_bootstrap->getResource( 'Auth' );
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout->disableLayout();
		
		if ( ! $auth->hasIdentity() ) {
			return $this->_redirect( '/' );
		}
		
		$auth->clearIdentity();
		Zend_Session::forgetMe();
        return $this->_redirect( '/' );
    }
	function getGroup( $id )
	{
		switch ( (int) $id ) {
			case 1: // Validating
			case 2: // гость
			case 5: // Banned
			default:
				return 'guest';
			break;
			case 3: // пользователь
			case 14: // новичок
			case 19: // ex–Morgenshtern
			case 9: // рекрут
				return 'user';
			break;
			case 12: // LuN
			case 13: // HONOR
			case 15: // Shk
			case 16: // Slayers
			case 17: // Bloodysunset
			case 18: // Exorcium
			case 20: // TBR
			case 21: // KHonour
				return 'ally';
			break;
			case 7: // клан
			case 8: // Совет
			case 6: // Глава
				return 'clan';
			break;
			case 4: // Админ
				return 'root';
			break;
		}
	}
}
