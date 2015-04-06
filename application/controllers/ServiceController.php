<?php

class ServiceController extends Zend_Controller_Action 
{
    protected $_cache = null;
    protected $_bot = null;
    protected $_logger = null;
    protected $_auth = null;

	function init()
    {
        $bootstrap = $this->getInvokeArg( 'bootstrap' );
        $manager = $bootstrap->getResource( 'CacheManager' );
		$this->_cache = $manager->getCache( 'block' );
		$botResource = $bootstrap->getPluginResource( 'Bot' );
		$this->_bot = $botResource->getRandomBot();
		$this->_logger = $bootstrap->getResource( 'Logger' );
		$this->_auth = $bootstrap->getResource( 'Auth' );

		$this->getResponse()->setHeader( 'Content-Type', 'text/html; charset=utf-8' );
    }
    public function indexAction() 
    {
        //
    }
    public function analizeAction() 
    {
		$nick = (string) $this->getRequest()->getParam( 'nick', 'Aliance spb' );
		try {
			$chars = $this->_bot->analizeBattle( $nick );
		} catch ( Morgenshtern_Bot_Exception $e ) {
			printf( '<p>Ошибка: %s</p>', $e->getMessage() );
		}
		$this->view->chars = $chars;
		$this->view->nick = $nick;
		
		/*
		$itemModel = new Application_Model_Item;
		
		$nick = (string) $this->getRequest()->getParam( 'nick', 'Aliance spb' );
		$charClass = '<i>не определён</i>';
		$info = $this->_bot->getCharInfo( $nick );

		foreach( $info['objects'] as $item ) {
			list( $title, $description ) = explode( '=', $item );
			$_description = explode( '\n', $description );
			$item = $itemModel->getItem( $_description[0] );
			if ( $item ) {
				foreach ( $item['requirements'] as $requirement ) {
					switch ( $requirement['title'] ) {
						case 'Мастерство владения арбалетом':
							$charClass = 'Арбалетчик';
						break;
						case 'Мастерство владения луком':
							$charClass = 'Лучник';
						break;
						default:
							$this->_logger->info( 'Класс бойца ' . $nick . ' не определен' );
							$charClass = '<i>не определён</i>';
						break;
					}
				}
			}
		}

		$this->view->nick = $nick;
		$this->view->charClass = $charClass;
		*/
	}
    public function alliesAction() 
    {
		$this->view->headLink()->appendStylesheet( 'http://fonts.googleapis.com/css?family=Droid+Sans', 'screen' );
		$this->view->headLink()->appendStylesheet( '/css/index.allies.css', 'screen' );
		$this->view->headLink()->appendStylesheet( '/css/global.table.css', 'screen' );
		$this->view->headScript()->appendFile( '/js/index.allies.js', 'text/javascript' );
		$this->view->headTitle()->prepend( 'Мониторинг союзников' );
		
        $charsModel = new Application_Model_IbfMorgenshternMembers;
		$diplomacyModel = new Application_Model_Diplomacy;
		$allies = $diplomacyModel->getAllies();

		$staff = $charsModel->getOnlineStaff();
		$chars = $staff;
		foreach ( $allies as $clan ) {
			$staff = $charsModel->getOnlineClanStaff( $clan['clan'] );
			$chars = array_merge( $chars, $staff );
		}
		$this->view->staff = $chars;
		$this->view->char = new Morgenshtern_Char;
		$user = $this->_auth->getIdentity();
		$this->view->login = $user->name;
		/*
		if ( ! $this->_cache->test( 'allies' ) ) {
			set_time_limit( 120 );
			
			$allies = array( 'Morgenshtern', 'HONOR', 'LuN', 'Shk', 'Slayers', 'Exorcium', 'Bloodysunset' );
			$alliesStaff = array();
			foreach ( $allies as $ally ) {
				$staff = $this->_bot->getClanOnlineStaff( $ally );
				$alliesStaff = array_merge( $alliesStaff, $staff );
			}
			$this->view->staff = $alliesStaff;
			$this->view->char = new Morgenshtern_Char;
		}
		$this->view->cache = $this->_cache;
		*/
    }
}