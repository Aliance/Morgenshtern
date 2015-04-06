<?php

class BotController extends Zend_Controller_Action 
{
    protected $_bot = null;
    protected $_logger = null;
    protected $_options = array();
    function init()
    {
        if ( $this->getRequest()->isXmlHttpRequest() ) {
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender();
            $this->getResponse()->setHeader( 'Content-Type', 'application/json; charset=utf-8' );
			Zend_Json::$useBuiltinEncoderDecoder = true;
        } else {
			$this->getResponse()->setHeader( 'Content-Type', 'text/html; charset=utf-8' );
		}

        $bootstrap = $this->getInvokeArg( 'bootstrap' );
        $this->_bot = $bootstrap->getPluginResource( 'Bot' );
        $this->_logger = $bootstrap->getResource( 'Logger' );
    }
    public function indexAction() 
    {
        $this->view->headLink()->appendStylesheet( '/css/global.forms.css', 'screen' );
		$this->view->headScript()->appendFile( '/js/bot.js', 'text/javascript' );
		$this->view->headTitle()->prepend( 'Клан бот' );
    }
    public function connectAction() 
    {
        if ( ! $this->getRequest()->isXmlHttpRequest() ) {
            return $this->_redirect( '/bot' );
        }
		$bot = ucfirst( $this->getRequest()->getPost( 'bot' ) );
		$method = 'init' . $bot;
		$start_time = microtime( 1 );
        $bot = $this->_bot->{$method}();
        $bot->connect();
        $end_time = microtime( 1 );
        $this->getResponse() ->appendBody( sprintf( '<p>Страница сгенерирована за %.10f секунд</p>', $end_time - $start_time ) );
    }
    public function pingAction() 
    {
        if ( ! $this->getRequest()->isXmlHttpRequest() ) {
            return $this->_redirect( '/bot' );
        }
		$bot = ucfirst( $this->getRequest()->getPost( 'bot' ) );
		die(''.$bot);
		$method = 'init' . $bot;
		$start_time = microtime( 1 );
        $bot = $this->_bot->{$method}();
        $bot->ping();
        $end_time = microtime( 1 );
        $this->getResponse() ->appendBody( sprintf( '<p>Страница сгенерирована за %.10f секунд</p>', $end_time - $start_time ) );
    }
    public function chatAction() 
    {
        if ( ! $this->getRequest()->isXmlHttpRequest() ) {
            return $this->_redirect( '/bot' );
        }
		$bot = ucfirst( $this->getRequest()->getPost( 'bot' ) );
		$user = (string) $this->getRequest()->getPost( 'user', 'Aliance spb' );
		$message = (string) $this->getRequest()->getPost( 'msg' );
		$_chars = (string) $this->getRequest()->getPost( 'chars' );
		$chars = explode( ', ', $_chars );
		$method = 'init' . $bot;
		$start_time = microtime( 1 );
        $bot = $this->_bot->{$method}();
        $std = new stdClass();
		try {
			$bot->chat( 'Сообщение от чара [' . $user . ']:', $chars, true );
			$bot->chat( $message, $chars, true );
			$bot->chat( 'Это сообщение было отправлено ботом клана Morgenshtern. Вы можете на него ответить через наш сайт.', $chars, true );
			$this->_logger->bot( 'Чат: [' . $user . '] [' . $_chars . '] [' . $message . ']' );
			$end_time = microtime( 1 );
			
			$std->generated = sprintf( '%.10f', $end_time - $start_time );
		} catch ( Morgenshtern_Bot_Exception $e ) {
			$std->error = $e->getMessage();
		}
        $this->getResponse() ->appendBody( Zend_Json::encode( $std ) );
    }
    public function updateAction() 
    {
        if ( ! $this->getRequest()->isXmlHttpRequest() ) {
            return $this->_redirect( '/bot' );
        }
		$bot = ucfirst( $this->getRequest()->getPost( 'bot' ) );
		$method = 'init' . $bot;
		$start_time = microtime( 1 );
        $bot = $this->_bot->{$method}();
        $bot->update();
        $end_time = microtime( 1 );
        $this->getResponse() ->appendBody( sprintf( '<p>Страница сгенерирована за %.10f секунд</p>', $end_time - $start_time ) );
    }
}